<template>
  <template v-if="!isDeleted">
    <div
        ref="rootEl"
        class="concrete-block"
        :data-concrete-block-id="blockId"
    >
      <div class="relative">
        <slot />
        <HotSpotOverlay
            :element="rootEl"
            :is-hovered="isBlockHovered"
            :is-active="isBlockClicked"
            hover-color="var(--color-concrete-green)"
            active-color="var(--color-concrete-green)"
            :hover-opacity="0.2"
            :active-opacity="0.4"
            :outset="8"

        />
      </div>
      <HotSpot
          :element="rootEl"
          :is-targeted="isBlockHovered || isBlockClicked"
          border-color="var(--color-concrete-block)"
          badge-placement="offset-top-center"
          :outset="8"

      >
        <template #badge="{ isHovered: isBadgeHovered, badgePlacement }">
          <HotSpotBadge
              :label="name || ''"
              :is-hovered="isBadgeHovered"
              :badge-placement="badgePlacement"
              :badge-color="{
            backgroundColor: 'var(--color-concrete-block)',
            textColor: 'var(--color-gray-800)',
          }"
          />
        </template>
      </HotSpot>
    </div>

  </template>

  <DeleteBlockModal
      :open="showDeleteModal"
      :block-id="blockId"
      :area-handle="areaHandle"
      :is-master-collection="isMasterCollectionBool"
      :page-id="pageId"
      :lang="parseLang?.delete ?? null"
      @update:open="showDeleteModal = $event"
  />
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from "vue"
import Menu from "./Block/Menu.vue";
import HotSpot from "./Ui/HotSpot.vue";
import HotSpotOverlay from "./Ui/HotSpotOverlay.vue";
import DeleteBlockModal from "./Block/DeleteBlockModal.vue";
import { normalizeJsonResponse, useAjax, useParsedJsonProp } from '@concretecms/backendui'
import { useConcreteUiStore } from '../stores/concrete-ui'
import type { DeleteBlockOperation } from '../stores/types/page-operations'
import { useBlockEditorRegistry } from '../stores/block-editor-registry'
import { useToast } from '../utilities/toast'
import HotSpotBadge from "./Ui/HotSpotBadge.vue";

const rootEl = ref<HTMLElement | null>()
const editMode = ref(false)
const isDeleted = ref(false)
const showDeleteModal = ref(false)
const toast = useToast()
const uiStore = useConcreteUiStore()
const blockEditorRegistry = useBlockEditorRegistry()
const { request } = useAjax()
const runningDeleteOperationId = ref<string | null>(null)

const props = defineProps({
  blockId: Number | String,
  areaHandle: String,
  pageId: Number | String,
  isMasterCollection: [Boolean, String, Number],
  name: String,
  variants: String | Array<{ file: String; name: String }>,
  blocktype: [Object, String],
  editor: [Object, String],
  lang: Object | String | null,
  selectedVariant: String,
  deleteToken: String
})

const parsedVariants = useParsedJsonProp(props.variants)
const parseBlockType = useParsedJsonProp(props.blocktype)
const parseEditor = useParsedJsonProp(props.editor)
const parseLang = useParsedJsonProp(props.lang)
const blockAreaPath = computed(() => {
  const rootNode = rootEl.value?.getRootNode()
  const hostElement = rootNode instanceof ShadowRoot ? rootNode.host : rootEl.value
  const startElement = (hostElement as HTMLElement | null) || rootEl.value?.parentElement
  if (!hostElement) {
    return []
  }

  const paths: string[] = []
  let current: HTMLElement | null = startElement
  while (current) {
    if (current.tagName === 'CONCRETE-AREA') {
      const areaPageId = current.getAttribute('page-id')
      const areaHandle = current.getAttribute('area-handle')

      if (areaPageId && areaHandle) {
        paths.push(`${areaPageId}:${areaHandle}`)
      }
    } else if (current.tagName === 'CONCRETE-CONTAINER') {
      const containerBlockId = current.getAttribute('container-block-id')
      if (containerBlockId) {
        paths.push(`container:${containerBlockId}`)
      }
    }
    current = current.parentElement
  }

  return paths
})

watch(
  [() => props.blockId, blockAreaPath],
  ([newBlockId, newPaths], [oldBlockId]) => {
    if (oldBlockId) {
      uiStore.clearBlockAreaMap(oldBlockId)
    }

    if (!newBlockId) {
      return
    }

    uiStore.setBlockAreaMap(newBlockId, newPaths)
  },
  { immediate: true, deep: true }
)

const isInteractionsEnabled = computed(() => Boolean((uiStore.page as any)?.interactionsEnabled ?? true))
const isMasterCollectionBool = computed(() => {
  const value = props.isMasterCollection
  return value === true || value === 1 || value === '1' || value === 'true'
})
const isBlockClicked = computed(() => isInteractionsEnabled.value && uiStore.clickProxy.activeElementId === props.blockId)
const isBlockDoubleClicked = computed(() => isInteractionsEnabled.value && uiStore.clickProxy.doubleClickedElementId === props.blockId)
const isBlockHovered = computed(() => {
  if (!isInteractionsEnabled.value) {
    return false
  }

  if (!uiStore.clickProxy.activeElementId) {
    const hovered = uiStore.clickProxy.hoverElementId === props.blockId
    return hovered
  }

  return false
})

watch(isBlockDoubleClicked, (value) => {
  if (!value) {
    return
  }

  editBlock()
})

const clickedOutlineColor = 'outline-concrete-green';
const hoveredOutlineColor = 'outline-concrete-green';
const outlineColor = computed(() => {
  if (isBlockClicked.value) return clickedOutlineColor
  if (isBlockHovered.value) return hoveredOutlineColor
  return 'outline-transparent'
})

let menuId = computed(() => 'm' + props.blockId + '-menu')
const isAddContentDragActive = computed(() => Boolean((uiStore.page as any)?.addContentDragActive))

function editBlock() {
  if (!currentEditorComponent.value) {
    return
  }

  uiStore.setPageInteractionsEnabled(false)
  editMode.value = true
}

function clearMenuState() {
  if (uiStore.clickProxy.activeElementId === props.id) {
    uiStore.clickProxy.hoverElementId = ''
    uiStore.clickProxy.activeElementId = ''
    uiStore.clickProxy.doubleClickedElementId = ''
    uiStore.clickProxy.activeElementMenuId = ''
  }
}

function handleUpdated() {
  uiStore.setPageInteractionsEnabled(true)
  editMode.value = false
}

function handleEditorClosed() {
  uiStore.setPageInteractionsEnabled(true)
  editMode.value = false
}

const currentEditorComponent = computed(() => {
  const editorComponentName = parseEditor?.component
  return blockEditorRegistry.resolveEditorComponent(editorComponentName)
})

const activeDeleteOperation = computed<DeleteBlockOperation | null>(() => {
  const operationId = uiStore.page.activeOperationId
  if (!operationId) {
    return null
  }

  const operation = uiStore.page.operationsQueue.find((item) => item.id === operationId)
  if (!operation || operation.type !== 'block.delete' || operation.status !== 'running') {
    return null
  }

  return operation
})

function matchesDeleteTarget(operation: DeleteBlockOperation) {
  return String(operation.pageBlock.bID) === String(props.blockId)
    && String(operation.pageBlock.arHandle) === String(props.areaHandle)
    && String(operation.pageBlock.cID || '') === String(props.pageId || '')
}

function runDeleteOperation(operation: DeleteBlockOperation) {
  runningDeleteOperationId.value = operation.id

  const endpoint = isMasterCollectionBool.value
      ? '/ccm/system/dialogs/block/delete/submit_all'
      : '/ccm/system/dialogs/block/delete/submit';

  const params = new URLSearchParams({
    cID: props.pageId,
    bID: props.blockId,
    arHandle: props.areaHandle,
    ccm_token: props.deleteToken,
  })

  const url = window.CCM_DISPATCHER_FILENAME + endpoint + '?' + params.toString();
  const body = operation.deleteAll ? { deleteAll: 1 } : {}

  request({
    url,
    method: 'POST',
    body,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)
      toast.success(
        normalizedResponse?.title || 'Deleted',
        normalizedResponse?.message || 'Block deleted successfully.'
      )

      // Remove the custom element host itself on delete. Keeping the host in place
      // leaves stale siblings/targets around because only the inner slot content is hidden.
      const hostId = String(props.id || '')
      const hostElement = hostId ? document.getElementById(hostId) : null
      const previousSibling = hostElement?.previousElementSibling || null
      const nextSibling = hostElement?.nextElementSibling || null
      if (hostElement) {
        hostElement.remove()
      } else {
        isDeleted.value = true
      }

      // PHP renders a target before and after blocks. Once the host is removed those
      // two targets can become adjacent duplicates, so collapse one of them.
      const prevIsTarget = previousSibling?.tagName === 'CONCRETE-AREA-BLOCK-TARGET'
      const nextIsTarget = nextSibling?.tagName === 'CONCRETE-AREA-BLOCK-TARGET'
      if (prevIsTarget && nextIsTarget && nextSibling) {
        nextSibling.remove()
      }

      clearMenuState()

      uiStore.completePageOperation(operation.id)
    },
    onError: () => {
      uiStore.failPageOperation(operation.id)
    },
    onComplete: () => {
      runningDeleteOperationId.value = null
    },
  })
}

watch(
  activeDeleteOperation,
  (operation) => {
    if (!operation || !matchesDeleteTarget(operation)) {
      return
    }

    if (runningDeleteOperationId.value === operation.id) {
      return
    }

    runDeleteOperation(operation)
  },
  { immediate: true }
)

onBeforeUnmount(() => {
  if (editMode.value) {
    uiStore.setPageInteractionsEnabled(true)
  }

  uiStore.clearBlockAreaMap(props.blockId)
})

</script>
