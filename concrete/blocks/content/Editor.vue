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
  normalizeJsonResponse,
  useAjax,
} from '@concretecms/backendui'
import MenuContainer from '../../../build/assets/cms/js/components/Ui/MenuContainer.vue'
import { useMenuPositioner } from '../../../build/assets/cms/js/utilities/menu'
import { useConcreteUiStore } from '../../../build/assets/cms/js/stores/concrete-ui'
import type { AddBlockOperation, AddBlockTargetRef, BlockRef, UpdateBlockOperation } from '../../../build/assets/cms/js/stores/types/page-operations'

const props = defineProps<{
  editor: {
    component: string
    componentProps?: {
      content?: string
    }
  }
  mode?: 'add' | 'edit'
  blockTypeId?: number
  blockId: string | number
  areaHandle: string
  pageId: string | number
  contentHtml?: string | null
  contentEl?: HTMLElement | null
  addTarget?: AddBlockTargetRef
  ignoreContainer?: boolean
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
const editorMode = computed(() => props.mode ?? 'edit')
const pendingOperationId = ref<string | null>(null)
const pendingOperationResponse = ref<any | null>(null)
const pendingOperationHasStarted = ref(false)
const pendingOperationReachedDone = ref(false)
const submitUrl = computed(() => {
  const params = new URLSearchParams({
    cID: String(props.pageId),
    arHandle: String(props.areaHandle),
  })

  if (editorMode.value === 'add') {
    params.set('btID', String(props.blockTypeId || 0))
    return `/ccm/system/dialogs/page/add_block/submit?${params.toString()}`
  }

  params.set('bID', String(props.blockId))
  return `/ccm/system/dialogs/block/edit/submit?${params.toString()}`
})

function handleSave() {
  if (isSubmitting.value || pendingOperationId.value) {
    return
  }

  isSubmitting.value = true

  const body = new FormData()
  body.set('content', contentHtml.value)
  if (editorMode.value === 'add') {
    body.set('ccm_token', String((window as any).CCM_SECURITY_TOKEN || ''))
    if (props.addTarget) {
      body.set('dragAreaBlockID', String(props.addTarget.afterBlockId || 0))
    }
  }

  request({
    url: submitUrl.value,
    method: 'POST',
    body,
    skipResponseValidation: true,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)

      if (editorMode.value === 'add' && props.addTarget) {
        const operation: AddBlockOperation = {
          id: `block.add.${String(props.blockTypeId || 0)}.${Date.now()}`,
          type: 'block.add',
          status: 'queued',
          blockTypeId: Number(props.blockTypeId || 0),
          ignoreContainer: Boolean(props.ignoreContainer ?? false),
          target: props.addTarget,
          response: normalizedResponse,
        }

        uiStore.enqueuePageOperation(operation)
        pendingOperationId.value = operation.id
        pendingOperationResponse.value = normalizedResponse
        pendingOperationHasStarted.value = false
        pendingOperationReachedDone.value = false
      } else {
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
        pendingOperationId.value = operation.id
        pendingOperationResponse.value = normalizedResponse
        pendingOperationHasStarted.value = false
        pendingOperationReachedDone.value = false
      }
    },
    onComplete: () => {
      // Keep the editor in place until the queued page operation has finished
      // replacing or inserting the rendered block on the page.
    },
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

watch(
  () => pendingOperationId.value
    ? uiStore.page.operationsQueue.find((operation) => operation.id === pendingOperationId.value) ?? null
    : null,
  (operation) => {
    if (!pendingOperationId.value) {
      return
    }

    if (!operation) {
      if (pendingOperationReachedDone.value || pendingOperationHasStarted.value) {
        const response = pendingOperationResponse.value
        pendingOperationId.value = null
        pendingOperationResponse.value = null
        pendingOperationHasStarted.value = false
        pendingOperationReachedDone.value = false
        isSubmitting.value = false
        emit('updated', { response })
        emit('closed')
      }
      return
    }

    if (operation.status === 'queued' || operation.status === 'running') {
      pendingOperationHasStarted.value = true
    }

    if (operation.status === 'done') {
      pendingOperationReachedDone.value = true
      const response = pendingOperationResponse.value
      pendingOperationId.value = null
      pendingOperationResponse.value = null
      pendingOperationHasStarted.value = false
      pendingOperationReachedDone.value = false
      isSubmitting.value = false
      emit('updated', { response })
      emit('closed')
      return
    }

    if (operation.status === 'failed') {
      pendingOperationId.value = null
      pendingOperationResponse.value = null
      pendingOperationHasStarted.value = false
      pendingOperationReachedDone.value = false
      isSubmitting.value = false
    }
  }
)

onMounted(() => {
  contentHtml.value = props.contentHtml ?? props.editor?.componentProps?.content ?? ''

  void nextTick(() => {
    menuPos.update()
  })
})

</script>
