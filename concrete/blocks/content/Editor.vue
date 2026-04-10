<template>
  <div class="relative">
    <MenuContainer>
      <div
        ref="menuEl"
        class="absolute z-50 -translate-x-1/2 pointer-events-auto"
        :style="{ left: menuLeft, top: menuTop }"
      >
        <BaselineToolbar toolbar-mode="inline">
          <template #actions>
            <InlineToolbarSeparator />
            <InlineToolbarGroup>
              <InlineToolbarButton :disabled="isSubmitting" @click="handleCancel">
                Cancel
              </InlineToolbarButton>
              <InlineToolbarButton :disabled="isSubmitting" @click="handleSave">
                Save
              </InlineToolbarButton>
            </InlineToolbarGroup>
          </template>
        </BaselineToolbar>
      </div>
    </MenuContainer>

    <div
      ref="editableEl"
      class="ccm-content-block-editor"
    >
      <BaselineEditor v-model="contentHtml" />
    </div>

  </div>
</template>

<script lang="ts">
import type { BlockEditorMeta } from '../../../build/assets/cms/js/stores/block-editor-registry'

export const blockEditorMeta: BlockEditorMeta = {
  pageContentMode: 'hide',
  placement: 'page',
  editorContentSource: 'html',
}
</script>

<style>
.ccm-content-block-editor div.tiptap {
  min-height: 32px;
}

.ccm-content-block-editor:focus-within div.tiptap {
  outline: none;
}
</style>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import {
  BaselineEditor,
  BaselineToolbar,
  InlineToolbarButton,
  InlineToolbarGroup,
  InlineToolbarSeparator,
} from '@concretecms/backendui'
import MenuContainer from '../../../build/assets/cms/js/components/Ui/MenuContainer.vue'
import { useBlockEditorSession } from '../../../build/assets/cms/js/components/Block/Editor/useBlockEditorSession'
import { useMenuPositioner } from '../../../build/assets/cms/js/utilities/menu'
import type { BlockEditorContext } from '../../../build/assets/cms/js/stores/types/block-editors'

const props = defineProps<{
  context: BlockEditorContext
}>()

const emit = defineEmits<{
  (e: 'updated', payload: { response: any }): void
  (e: 'closed'): void
}>()

const editableEl = ref<HTMLElement | null>(null)
const menuEl = ref<HTMLElement | null>(null)
const contentHtml = ref('')
const alwaysEnabled = ref(true)

const menuPos = useMenuPositioner(editableEl, menuEl, alwaysEnabled)
const menuLeft = computed(() => `${menuPos.x.value}px`)
const menuTop = computed(() => `${menuPos.y.value}px`)
const { isSubmitting, submit, submitUrl } = useBlockEditorSession(
  computed(() => props.context),
  {
    onUpdated: (payload) => emit('updated', payload),
    onClosed: () => emit('closed'),
  }
)

function handleSave() {
  const body = new FormData()
  body.set('content', contentHtml.value)

  submit({
    url: submitUrl.value,
    body,
  })
}

function handleCancel() {
  emit('closed')
}

watch(contentHtml, () => {
  void nextTick(() => {
    menuPos.update()
  })
})

onMounted(() => {
  contentHtml.value = props.context.mode === 'edit'
    ? props.context.operation.contentHtml ?? props.context.editor.componentProps?.content ?? ''
    : props.context.editor.componentProps?.content ?? ''

  void nextTick(() => {
    menuPos.update()
  })
})

</script>
