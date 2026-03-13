<template>
  <div class="relative">
    <MenuContainer>
      <div
        ref="menuEl"
        class="absolute z-50 -translate-x-1/2 pointer-events-auto"
        :style="{ left: menuLeft, top: menuTop }"
      >
        <InlineToolbar class="flex-nowrap">
          <InlineToolbarGroup>
            <InlineToolbarButton :disabled="isSubmitting" @click="handleCancel">
              Cancel
            </InlineToolbarButton>
            <InlineToolbarButton :disabled="isSubmitting" @click="handleSave">
              Save
            </InlineToolbarButton>
          </InlineToolbarGroup>
        </InlineToolbar>
      </div>
    </MenuContainer>

    <div
      ref="editableEl"
      class="min-h-[48px] rounded border border-concrete-green/30 bg-base-100 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-concrete-green/30"
      contenteditable="true"
      @input="handleInput"
    ></div>

  </div>
</template>

<script lang="ts">
import type { BlockEditorMeta } from '../../../build/assets/cms/js/stores/block-editor-registry'

export const blockEditorMeta: BlockEditorMeta = {
  pageContentMode: 'hide',
  editorContentSource: 'html',
}
</script>

<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import {
  InlineToolbar,
  InlineToolbarButton,
  InlineToolbarGroup,
  normalizeJsonResponse,
  useAjax,
} from '@concretecms/backendui'
import MenuContainer from '../../../build/assets/cms/js/components/Ui/MenuContainer.vue'
import { useMenuPositioner } from '../../../build/assets/cms/js/utilities/menu'
import { useConcreteUiStore } from '../../../build/assets/cms/js/stores/concrete-ui'
import type { BlockRef, UpdateBlockOperation } from '../../../build/assets/cms/js/stores/types/page-operations'

const props = defineProps<{
  editor: {
    component: string
    componentProps?: {
      content?: string
    }
  }
  blockId: string | number
  areaHandle: string
  pageId: string | number
  contentHtml?: string | null
  contentEl?: HTMLElement | null
}>()

const emit = defineEmits<{
  (e: 'updated', payload: { response: any }): void
  (e: 'closed'): void
}>()

const { request } = useAjax()
const uiStore = useConcreteUiStore()
const editableEl = ref<HTMLElement | null>(null)
const menuEl = ref<HTMLElement | null>(null)
const isSubmitting = ref(false)
const contentHtml = ref('')
const alwaysEnabled = ref(true)

const menuPos = useMenuPositioner(editableEl, menuEl, alwaysEnabled)
const menuLeft = computed(() => `${menuPos.x.value}px`)
const menuTop = computed(() => `${menuPos.y.value}px`)
const dialogUrl = computed(() => {
  const params = new URLSearchParams({
    cID: String(props.pageId),
    arHandle: String(props.areaHandle),
    bID: String(props.blockId),
  })

  return `/ccm/system/dialogs/block/edit/submit?${params.toString()}`
})


function handleInput() {
  contentHtml.value = editableEl.value?.innerHTML ?? ''
  void nextTick(() => {
    menuPos.update()
  })
}

function handleSave() {
  if (isSubmitting.value) {
    return
  }

  isSubmitting.value = true

  const body = new FormData()
  body.set('content', contentHtml.value)

  request({
    url: dialogUrl.value,
    method: 'POST',
    body,
    skipResponseValidation: true,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)

      const originalBlock: BlockRef = {
        bID: props.blockId,
        arHandle: props.areaHandle,
        cID: props.pageId,
      }
      const updatedBlock: BlockRef = {
        bID: normalizedResponse?.bID || originalBlock.bID,
        arHandle: normalizedResponse?.arHandle || originalBlock.arHandle,
        cID: normalizedResponse?.cID || originalBlock.cID,
      }

      const operation: UpdateBlockOperation = {
        id: `block.update.${String(originalBlock.bID)}.${Date.now()}`,
        type: 'block.update',
        status: 'queued',
        originalBlock,
        updatedBlock,
        replacementHtml: typeof normalizedResponse?.html === 'string' ? normalizedResponse.html : undefined,
        response: normalizedResponse,
      }

      uiStore.enqueuePageOperation(operation)
      emit('updated', { response: normalizedResponse })
      emit('closed')
    },
    onComplete: () => {
      isSubmitting.value = false
    },
  })
}

function handleCancel() {
  emit('closed')
}

onMounted(() => {
  contentHtml.value = props.contentHtml ?? props.editor?.componentProps?.content ?? ''
  if (editableEl.value) {
    editableEl.value.innerHTML = contentHtml.value
  }

  void nextTick(() => {
    menuPos.update()
  })
})

</script>
