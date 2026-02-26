<template>
  <HotSpot
      v-if="!isDeleted"
      :item-id="id"
      :menu-id="menuId"
      hover-outline-color="outline-concrete-green"
      active-outline-color="outline-concrete-green"
      active-bg-class="bg-concrete-green/30"
      @dblclick="editBlock"
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
        :edit-action="editAction"
        :dialog-title="editDialogTitle"
        :dialog-width="editDialogWidth"
        :dialog-height="editDialogHeight"
        @updated="handleUpdated"
      />
    </div>
    <slot />
  </HotSpot>

  <DeleteBlockModal
      :open="showDeleteModal"
      :delete-action="deleteAction"
      :delete-all-action="deleteAllAction"
      :message="deleteMessage"
      :defaults-message="deleteDefaultsMessage"
      :block-id="deleteBlockId"
      :area-handle="deleteAreaHandle"
      :is-master-collection="deleteIsMasterCollection"
      :dialog-title="deleteDialogTitle"
      :progressive-operation-title="deleteProgressiveOperationTitle"
      @update:open="showDeleteModal = $event"
      @deleted="handleDeleted"
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
import { computed, getCurrentInstance, ref } from "vue"
import HotSpot from "./Ui/HotSpot.vue"
import Menu from "./Block/Menu.vue";
import DeleteBlockModal from "./Block/DeleteBlockModal.vue";
import { useParsedJsonProp } from '@concretecms/backendui'
import { useBlockEditorRegistry } from '@concretecms/backendui'
import { useUiStore } from '@concretecms/backendui'
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
const toastTitle = ref('Update Block')
const toastDescription = ref('The block has been saved successfully.')
const uiStore = useUiStore()

const props = defineProps({
  id: String,
  name: String,
  variants: String | Array<{ file: String; name: String }>,
  blocktype: String | Object,
  selectedVariant: String,
  editAction: String,
  editDialogTitle: String,
  editDialogWidth: Number | String,
  editDialogHeight: Number | String,
  deleteAction: String,
  deleteAllAction: String,
  deleteMessage: String,
  deleteDefaultsMessage: String,
  deleteBlockId: Number | String,
  deleteAreaHandle: String,
  deleteIsMasterCollection: Boolean | String | Number,
  deleteDialogTitle: String,
  deleteProgressiveOperationTitle: String,
})

const parsedVariants = useParsedJsonProp(props.variants)
const parseBlockType = useParsedJsonProp(props.blocktype)
const instance = getCurrentInstance()

let menuId = computed(() => props.id + '-menu')

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

function handleDeleted(response: any) {
  isDeleted.value = true
  clearMenuState()

  const parsedAreaId = parseInt(response?.aID, 10)
  const parsedBlockId = parseInt(response?.bID, 10)
  if (Number.isNaN(parsedAreaId) || Number.isNaN(parsedBlockId)) {
    return
  }

  const editor = (window as any).Concrete?.getEditMode?.()
  const area = editor?.getAreaByID?.(parsedAreaId)
  const block = area?.getBlockByID?.(parsedBlockId)

  ;(window as any).ConcreteEvent?.fire?.('EditModeBlockDeleteComplete', {
    block: block
  })
}

function executeScripts(scripts: HTMLScriptElement[]) {
  scripts.forEach((script) => {
    const node = document.createElement('script')
    if (script.src) {
      node.src = script.src
    } else {
      node.textContent = script.textContent
    }
    document.body.appendChild(node)
    node.remove()
  })
}

function getHostElement(): HTMLElement | null {
  const rootNode = (instance?.vnode?.el as any)?.getRootNode?.()
  const host = rootNode?.host
  return host instanceof HTMLElement ? host : null
}

function syncHostAttributes(host: HTMLElement, replacement: HTMLElement) {
  const currentAttributes = Array.from(host.attributes)
  currentAttributes.forEach((attribute) => {
    host.removeAttribute(attribute.name)
  })

  Array.from(replacement.attributes).forEach((attribute) => {
    host.setAttribute(attribute.name, attribute.value)
  })
}

function syncHostContent(host: HTMLElement, replacement: HTMLElement) {
  while (host.firstChild) {
    host.removeChild(host.firstChild)
  }

  Array.from(replacement.childNodes).forEach((child) => {
    host.appendChild(child.cloneNode(true))
  })
}

function handleUpdated(payload: { response: any; html: string }) {
  const host = getHostElement()
  if (!host || !payload?.html) {
    return
  }

  const parser = document.createElement('div')
  parser.innerHTML = payload.html
  const scripts = Array.from(parser.querySelectorAll('script'))
  scripts.forEach((script) => script.remove())

  const replacement = (
    parser.querySelector('concrete-block')
    || parser.querySelector('.ccm-block-edit')
    || parser.firstElementChild
  ) as HTMLElement | null
  if (!replacement) {
    return
  }

  syncHostAttributes(host, replacement)
  syncHostContent(host, replacement)
  executeScripts(scripts)
  editMode.value = false

  toastTitle.value = payload?.response?.title || 'Update Block'
  toastDescription.value = payload?.response?.message || 'The block has been saved successfully.'
  toastOpen.value = false
  toastOpen.value = true

  const parsedAreaId = parseInt(payload?.response?.aID, 10)
  const parsedBlockId = parseInt(payload?.response?.bID, 10)
  if (Number.isNaN(parsedAreaId) || Number.isNaN(parsedBlockId)) {
    return
  }

  const editor = (window as any).Concrete?.getEditMode?.()
  const area = editor?.getAreaByID?.(parsedAreaId)
  const block = area?.getBlockByID?.(parsedBlockId)
  ;(window as any).ConcreteEvent?.fire?.('EditModeUpdateBlockComplete', {
    block: block
  })
}

const registry = useBlockEditorRegistry();
const currentEditorComponent = computed(() => {
  const editorComponent = parseBlockType?.editors?.edit?.component ?? parseBlockType?.editor?.component
  return registry.getEditorComponent(editorComponent);
});

</script>
