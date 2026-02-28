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
import { computed, ref } from "vue"
import HotSpot from "./Ui/HotSpot.vue"
import Menu from "./Block/Menu.vue";
import DeleteBlockModal from "./Block/DeleteBlockModal.vue";
import DialogEditor from "./Block/Editor/DialogEditor.vue";
import ComposableEditor from "./Block/Editor/ComposableEditor.vue";
import InlineEditor from "./Block/Editor/InlineEditor.vue";
import { useParsedJsonProp } from '@concretecms/backendui'
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

function handleDeleted(response: any) {
  isDeleted.value = true
  clearMenuState()
}

function handleUpdated(payload: { response: any }) {
  editMode.value = false

  toastTitle.value = payload?.response?.title || 'Update Block'
  toastDescription.value = payload?.response?.message || 'The block has been saved successfully.'
  toastOpen.value = false
  toastOpen.value = true
}

const editorComponents: Record<string, any> = {
  DialogEditor,
  ComposableEditor,
  InlineEditor,
}

const currentEditorComponent = computed(() => {
  const editorComponentKey = parseBlockType?.editors?.edit?.component ?? parseBlockType?.editor?.component
  if (!editorComponentKey || typeof editorComponentKey !== 'string') {
    return null
  }

  return editorComponents[editorComponentKey] ?? null
});

</script>
