<template>
  <template v-if="!isDeleted">
    <div
        ref="rootEl"
        class="concrete-block"
        :data-concrete-block-id="blockId"
    >
      <div class="concrete-block">
        <div
            ref="contentEl"
            class="concrete-block__content"
            v-show="shouldShowPageContent"
        >
          <slot />
        </div>
        <HotSpotOverlay
            v-if="shouldRenderHotSpots"
            :element="rootEl"
            :data-concrete-block-id="blockId"
            :is-hovered="isBlockHovered"
            :is-active="isBlockClicked"
            hover-color="var(--color-concrete-green)"
            active-color="var(--color-concrete-green)"
            :hover-opacity="0.2"
            :active-opacity="0.4"
            :outset="2"
        />
      </div>
      <HotSpot
          v-if="shouldRenderHotSpots"
          :element="rootEl"
          :is-targeted="isBlockHovered || isBlockClicked"
          border-color="var(--color-concrete-block)"
          badge-placement="middle-center"
          :outset="2"

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

      <Menu
          :block-element="rootEl"
          :show="isBlockClicked"
          :id="menuId"
          :variants="parsedVariants"
          :selected-variant="selectedVariant"
          @edit="editBlock"
          @delete="showDeleteModal = true"
      >
      </Menu>

    </div>

  </template>

  <!-- Edit Mode for the block //-->
  <div v-if="editMode">
    <component
        :is="currentEditorComponent"
        v-if="currentEditorComponent"
        :block-type-id="parseBlockType?.id"
        :editor="parseEditor"
        :block-id="blockId"
        :area-handle="areaHandle"
        :page-id="pageId"
        :content-html="contentHtml"
        :content-el="contentEl"
        @updated="handleUpdated"
        @closed="handleEditorClosed"
    />
  </div>

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
import { normalizeJsonResponse, useAjax, useParsedJsonPropRef } from '@concretecms/backendui'
import { useConcreteUiStore } from '../stores/concrete-ui'
import type { DeleteBlockOperation } from '../stores/types/page-operations'
import { useBlockEditorRegistry } from '../stores/block-editor-registry'
import { useToast } from '../utilities/toast'
import HotSpotBadge from "./Ui/HotSpotBadge.vue";

const rootEl = ref<HTMLElement | null>()
const contentEl = ref<HTMLElement | null>()
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
})

const parsedVariants = useParsedJsonPropRef(() => props.variants)
const parseBlockType = useParsedJsonPropRef(() => props.blocktype)
const parseEditor = useParsedJsonPropRef(() => props.editor)
const parseLang = useParsedJsonPropRef(() => props.lang)
// Metadata exported by the active editor component. This lets the editor decide
// whether page content stays visible during edit mode and whether it needs access
// to the current block content from the page.
const editorMeta = ref({
  pageContentMode: 'preserve' as const,
  editorContentSource: 'none' as const,
})
// Snapshot of the rendered block content. Editors like the content block editor
// can use this instead of relying on backend-provided initial content props.
const contentHtml = computed(() => contentEl.value?.innerHTML ?? '')

watch(
  () => parseEditor.value?.component,
  async (componentName) => {
    editorMeta.value = await blockEditorRegistry.resolveEditorMeta(componentName)
  },
  { immediate: true }
)

const shouldShowPageContent = computed(() => {
  if (!editMode.value) {
    return true
  }

  // Editors can temporarily hide the page-rendered content while they take over
  // the editing UI. The default remains "preserve" for backward compatibility.
  return editorMeta.value.pageContentMode !== 'hide'
})

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
const shouldRenderHotSpots = computed(() => !editMode.value && isInteractionsEnabled.value)
const isMasterCollectionBool = computed(() => {
  const value = props.isMasterCollection
  return value === true || value === 1 || value === '1' || value === 'true'
})
const isBlockClicked = computed(() => isInteractionsEnabled.value && uiStore.clickProxy.activeElementId === props.blockId)
const isBlockDoubleClicked = computed(() => isInteractionsEnabled.value && uiStore.clickProxy.doubleClickedElementId === props.blockId)
const isAddContentDragActive = computed(() => Boolean((uiStore.page as any)?.addContentDragActive))

const isBlockHovered = computed(() => {
  if (!isInteractionsEnabled.value || isAddContentDragActive.value) {
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

let menuId = computed(() => 'm' + props.blockId + '-menu')

function editBlock() {
  if (!currentEditorComponent.value) {
    return
  }

  uiStore.setPageInteractionsEnabled(false)
  editMode.value = true
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
  const editorComponentName = parseEditor.value?.component
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
      const hostElement = document.querySelector(`concrete-block[block-id="${props.blockId}"]`)
      const trailingTarget = document.querySelector(
        `concrete-area-block-target[page-id="${String(props.pageId)}"][area-handle="${String(props.areaHandle)}"][after-block-id="${String(props.blockId)}"]`
      )
      if (hostElement) {
        hostElement.remove()
      } else {
        isDeleted.value = true
      }

      // Remove the trailing add target that belongs to the deleted block. Looking it up
      // by attributes is more reliable than nextElementSibling now that blocks may be
      // wrapped in layout containers during live insertion.
      if (trailingTarget instanceof HTMLElement) {
        trailingTarget.remove()
      }
      uiStore.refreshPageAreas()
      
      uiStore.completePageOperation(operation.id)
    },
    onError: (error: any) => {
      uiStore.failPageOperation(operation.id)

      const message = error?.responseText || error?.message || 'An unknown error occurred.'
      const globalWindow = window as any
      if (globalWindow?.ConcreteAlert?.dialog) {
        globalWindow.ConcreteAlert.dialog('Error', message)
        return
      }

      if (globalWindow?.ConcreteAlert?.error) {
        globalWindow.ConcreteAlert.error(message)
        return
      }

      window.alert(String(message).replace(/<[^>]*>/g, ''))
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
