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
          :id="menuId"
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
</template>

<script setup lang="ts">
import { computed, ref } from "vue"
import HotSpot from "./Ui/HotSpot.vue"
import Menu from "./Block/Menu.vue";
import DeleteBlockModal from "./Block/DeleteBlockModal.vue";
import { useParsedJsonProp } from '@concretecms/backendui'
import { useBlockEditorRegistry } from '@concretecms/backendui'
import { useUiStore } from '@concretecms/backendui'

const editMode = ref(false)
const editorRenderKey = ref(0)
const isDeleted = ref(false)
const showDeleteModal = ref(false)
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

const registry = useBlockEditorRegistry();
const currentEditorComponent = computed(() => {
  return registry.getEditorComponent(parseBlockType?.editor?.component);
});

</script>
