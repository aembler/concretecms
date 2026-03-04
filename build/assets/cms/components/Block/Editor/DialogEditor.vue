<template>
  <LazyDialog
    ref="lazyDialogRef"
    v-model:open="open"
    :src="dialogUrl"
    :dialog-title="dialogTitle"
    :dialog-width="dialogWidth"
    :dialog-height="dialogHeight"
    :allow-script-execution="true"
    :content-transform="transformDialogHtml"
  >
    <template #header>
      <DialogHeader>
        <div class="flex w-full min-w-0 items-center gap-2">
          <DialogTitle>{{ dialogTitle }}</DialogTitle>
          <div
            v-if="helpTooltipText"
            class="tooltip tooltip-left dialog-help-tooltip ms-auto me-1"
            :class="{ 'tooltip-open': helpTooltipOpen }"
            :data-tip="helpTooltipText"
          >
            <button
              type="button"
              class="inline-flex h-6 w-6 items-center justify-center rounded-full text-current/85 transition-colors hover:bg-white/15 hover:text-current"
              aria-label="Help"
              @click.stop="toggleHelpTooltip"
            >
              <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4 stroke-current">
                <circle cx="12" cy="12" r="9" stroke-width="1.75" />
                <path d="M9.5 9.5a2.5 2.5 0 0 1 5 0c0 2-2.5 2-2.5 4" stroke-width="1.75" stroke-linecap="round" />
                <circle cx="12" cy="17.25" r="1" fill="currentColor" stroke="none" />
              </svg>
            </button>
          </div>
        </div>
      </DialogHeader>
    </template>
    <template #footer>
      <DialogFooter>
        <button type="button" class="btn btn-secondary me-auto" :disabled="isSubmitting" @click="handleCancel">Cancel</button>
        <button type="button" class="btn btn-primary" :disabled="isSubmitting" @click="handleSave">Save</button>
      </DialogFooter>
    </template>
  </LazyDialog>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import {
  DialogFooter,
  DialogHeader,
  DialogTitle,
  LazyDialog,
  normalizeJsonResponse,
  useAjax
} from '@concretecms/backendui'
import { useConcreteUiStore } from '../../../stores/concrete-ui'
import type { AddBlockOperation, AddBlockTargetRef, BlockRef, UpdateBlockOperation } from '../../../stores/types/page-operations'

const props = defineProps<{
  editor: {
    component: string
    props: {
      dialogTitle: string
      dialogWidth: string | number
      dialogHeight: string | number
    }
  }
  mode?: 'add' | 'edit'
  blockTypeId?: number
  blockId: string | number
  areaHandle: string
  pageId: string | number
  addTarget?: AddBlockTargetRef
}>()

const open = ref(false)
const helpTooltipText = ref('')
const helpTooltipOpen = ref(false)
const isSubmitting = ref(false)
const { request } = useAjax()
const lazyDialogRef = ref<any>(null)
const uiStore = useConcreteUiStore()

const emit = defineEmits<{
  (e: 'updated', payload: { response: any }): void
  (e: 'closed'): void
}>()

const dialogTitle = computed(() => props.editor.props.dialogTitle)
const dialogWidth = computed(() => props.editor.props.dialogWidth)
const dialogHeight = computed(() => props.editor.props.dialogHeight)
const editorMode = computed(() => props.mode ?? 'edit')
const dialogUrl = computed(() => {
  const params = new URLSearchParams({
    cID: String(props.pageId),
    arHandle: String(props.areaHandle),
  })
  if (editorMode.value === 'add') {
    params.set('btID', String(props.blockTypeId || 0))
    return `/ccm/system/dialogs/page/add_block?${params.toString()}`
  }
  params.set('bID', String(props.blockId))
  return `/ccm/system/dialogs/block/edit?${params.toString()}`
})

function transformDialogHtml(rawHtml: string): string {
  const parser = document.createElement('div')
  parser.innerHTML = rawHtml

  const helpNodes = Array.from(parser.querySelectorAll('.dialog-help'))
  const helpText = helpNodes
    .map((node) => (node.textContent || '').trim())
    .filter(Boolean)
    .join(' ')
  helpTooltipText.value = helpText
  helpNodes.forEach((node) => node.remove())

  const legacyDialogButtons = Array.from(parser.querySelectorAll('.dialog-buttons'))
  legacyDialogButtons.forEach((node) => node.remove())
  const legacyActionButtons = Array.from(
    parser.querySelectorAll('button[data-dialog-action="submit"], button[data-dialog-action="cancel"]')
  )
  legacyActionButtons.forEach((node) => node.remove())

  return parser.innerHTML
}

function toggleHelpTooltip() {
  helpTooltipOpen.value = !helpTooltipOpen.value
}

function handleDocumentClick() {
  helpTooltipOpen.value = false
}

function findPrimaryForm(): HTMLFormElement | null {
  const contentEl = lazyDialogRef.value?.getContentElement?.()
  if (!contentEl) {
    return null
  }

  const selectors = ['#ccm-block-form', '[data-dialog-form]', 'form']
  for (const selector of selectors) {
    const form = contentEl.querySelector(selector)
    if (form instanceof HTMLFormElement) {
      return form
    }
  }

  return null
}

function normalizeMethod(method: string): 'GET' | 'POST' | 'PUT' | 'DELETE' {
  const normalized = (method || 'POST').toUpperCase()
  return ['GET', 'POST', 'PUT', 'DELETE'].includes(normalized) ? normalized as 'GET' | 'POST' | 'PUT' | 'DELETE' : 'POST'
}

function hasResponseErrors(response: any): boolean {
  return Boolean(
    response?.error
    || (Array.isArray(response?.errors) && response.errors.length > 0)
  )
}

function handleSave() {
  if (isSubmitting.value) {
    return
  }

  const form = findPrimaryForm()
  if (!form) {
    return
  }

  const formData = new FormData(form)
  if (editorMode.value === 'add' && props.addTarget) {
    formData.set('dragAreaBlockID', String(props.addTarget.afterBlockId || 0))
  }
  const url = form.getAttribute('action') || dialogUrl.value
  const method = normalizeMethod(form.getAttribute('method') || 'POST')
  if (!url) {
    return
  }

  isSubmitting.value = true
  request({
    url,
    method,
    body: formData,
    skipResponseValidation: true,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)

      if (hasResponseErrors(normalizedResponse)) {
        return
      }

      if (editorMode.value === 'add' && props.addTarget) {
        const operation: AddBlockOperation = {
          id: `block.add.${String(props.blockTypeId || 0)}.${Date.now()}`,
          type: 'block.add',
          status: 'queued',
          blockTypeId: Number(props.blockTypeId || 0),
          target: props.addTarget,
          response: normalizedResponse,
        }
        uiStore.enqueuePageOperation(operation)
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
      }
      emit('updated', { response: normalizedResponse })
      open.value = false
      helpTooltipOpen.value = false
    },
    onComplete: () => {
      isSubmitting.value = false
    }
  })
}

function handleCancel() {
  open.value = false
  helpTooltipOpen.value = false
}

watch(open, (isOpen) => {
  if (!isOpen) {
    emit('closed')
  }
})

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  open.value = true
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>
