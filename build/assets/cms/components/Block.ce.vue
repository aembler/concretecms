<template>
  <HotSpot
      v-if="!isDeleted"
      :item-id="id"
      :menu-id="menuId"
      :active="!isAddContentDragActive"
      hover-outline-color="outline-concrete-green"
      active-outline-color="outline-concrete-green"
      active-bg-class="bg-concrete-green/30"
      @dblclick="editBlock"
      class="min-h-[16px]"
  >
    <template #badge>
      {{ name }}
    </template>
    <template #menu>
      <Menu
          :variants="parsedVariants"
          :selected-variant="selectedVariant"
          @edit="editBlock"
          @delete="showDeleteModal = true"
      >
      </Menu>
    </template>
    <div v-if="editMode">
      <component
        :is="currentEditorComponent"
        v-if="currentEditorComponent"
        :key="editorRenderKey"
        :block-type-id="parseBlockType?.id"
        :editor="parseBlockType?.editors?.edit ?? null"
        :block-id="blockId"
        :area-handle="areaHandle"
        :page-id="pageId"
        @updated="handleUpdated"
      />
    </div>
    <slot />
  </HotSpot>

  <DeleteBlockModal
      :open="showDeleteModal"
      :block-id="blockId"
      :area-handle="areaHandle"
      :is-master-collection="isMasterCollection"
      :page-id="pageId"
      @update:open="showDeleteModal = $event"
  />

  <ToastProvider :duration="3000" swipe-direction="right">
    <Toast :open="toastOpen" variant="success" @update:open="toastOpen = $event">
      <div class="grid gap-1">
        <ToastTitle>{{ toastTitle }}</ToastTitle>
        <ToastDescription>{{ toastDescription }}</ToastDescription>
      </div>
      <ToastClose />
    </Toast>
    <ToastViewport />
  </ToastProvider>
</template>

<script setup lang="ts">
import { computed, ref, watch } from "vue"
import HotSpot from "./Ui/HotSpot.vue"
import Menu from "./Block/Menu.vue";
import DeleteBlockModal from "./Block/DeleteBlockModal.vue";
import DialogEditor from "./Block/Editor/DialogEditor.vue";
import ComposableEditor from "./Block/Editor/ComposableEditor.vue";
import InlineEditor from "./Block/Editor/InlineEditor.vue";
import { normalizeJsonResponse, useAjax, useParsedJsonProp } from '@concretecms/backendui'
import { useConcreteUiStore } from '../stores/concrete-ui'
import type { DeleteBlockOperation } from '../stores/types/page-operations'
import { buildDeleteBlockUrl } from '../support/dom/DeleteBlock'
import {
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport
} from '@concretecms/backendui'

const editMode = ref(false)
const editorRenderKey = ref(0)
const isDeleted = ref(false)
const showDeleteModal = ref(false)
const toastOpen = ref(false)
const toastTitle = ref('Deleted')
const toastDescription = ref('Block deleted successfully.')
const uiStore = useConcreteUiStore()
const { request } = useAjax()
const runningDeleteOperationId = ref<string | null>(null)

const props = defineProps({
  id: String, // Needed as a separate prop for DOM operations, menu objserver.
  blockId: Number | String,
  areaHandle: String,
  pageId: Number | String,
  isMasterCollection: Boolean | String | Number,
  name: String,
  variants: String | Array<{ file: String; name: String }>,
  blocktype: String | Object,
  selectedVariant: String,
})

const parsedVariants = useParsedJsonProp(props.variants)
const parseBlockType = useParsedJsonProp(props.blocktype)

let menuId = computed(() => props.id + '-menu')
const isAddContentDragActive = computed(() => Boolean((uiStore.page as any)?.addContentDragActive))

function editBlock() {
  editMode.value = true
  editorRenderKey.value += 1
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
  editMode.value = false
}

const editorComponents: Record<string, any> = {
  DialogEditor,
  ComposableEditor,
  InlineEditor,
}

const currentEditorComponent = computed(() => {
  const editorComponentKey = parseBlockType?.editors?.edit?.component
  if (!editorComponentKey || typeof editorComponentKey !== 'string') {
    return null
  }

  return editorComponents[editorComponentKey] ?? null
});

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

  const url = buildDeleteBlockUrl(
    operation.pageBlock.cID,
    operation.pageBlock.bID,
    operation.pageBlock.arHandle,
    operation.deleteAll
  )
  const body = operation.deleteAll ? { deleteAll: 1 } : {}

  request({
    url,
    method: 'POST',
    body,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)
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

      toastTitle.value = normalizedResponse?.title || 'Deleted'
      toastDescription.value = normalizedResponse?.message || 'Block deleted successfully.'
      toastOpen.value = false
      toastOpen.value = true

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

</script>
