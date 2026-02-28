<template>
  <LazyDialog
    ref="lazyDialogRef"
    v-model:open="open"
    :src="props.editAction"
    :dialog-title="props.dialogTitle || 'Edit Block'"
    :dialog-width="props.dialogWidth"
    :dialog-height="props.dialogHeight"
    :allow-script-execution="true"
    :content-transform="transformDialogHtml"
  >
    <template #header>
      <DialogHeader>
        <div class="flex w-full min-w-0 items-center gap-2">
          <DialogTitle>{{ props.dialogTitle || 'Edit Block' }}</DialogTitle>
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
import { onBeforeUnmount, onMounted, ref } from 'vue'
import {
  DialogFooter,
  DialogHeader,
  DialogTitle,
  LazyDialog,
  normalizeJsonResponse,
  useAjax
} from '@concretecms/backendui'

const props = withDefaults(defineProps<{
  editAction?: string
  dialogTitle?: string
  dialogWidth?: string | number
  dialogHeight?: string | number
}>(), {
  editAction: '',
  dialogTitle: '',
  dialogWidth: 'auto',
  dialogHeight: 'auto',
})

const open = ref(false)
const helpTooltipText = ref('')
const helpTooltipOpen = ref(false)
const isSubmitting = ref(false)
const { request } = useAjax()
const lazyDialogRef = ref<any>(null)

const emit = defineEmits<{
  (e: 'updated', payload: { response: any }): void
}>()

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

  const blockForm = contentEl.querySelector('#ccm-block-form')
  if (blockForm instanceof HTMLFormElement) {
    return blockForm
  }

  const dialogForm = contentEl.querySelector('[data-dialog-form]')
  if (dialogForm instanceof HTMLFormElement) {
    return dialogForm
  }

  const firstForm = contentEl.querySelector('form')
  return firstForm instanceof HTMLFormElement ? firstForm : null
}

function normalizeMethod(method: string): 'GET' | 'POST' | 'PUT' | 'DELETE' {
  const normalized = (method || 'POST').toUpperCase()
  if (normalized === 'GET' || normalized === 'POST' || normalized === 'PUT' || normalized === 'DELETE') {
    return normalized
  }

  return 'POST'
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
  const url = form.getAttribute('action') || props.editAction || ''
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

      if (normalizedResponse?.error || (Array.isArray(normalizedResponse?.errors) && normalizedResponse.errors.length > 0)) {
        return
      }

      const blockInfo = {
        bID: Number(normalizedResponse?.bID || 0),
        arHandle: String(normalizedResponse?.arHandle || ''),
        cID: Number(normalizedResponse?.cID || (window as any).CCM_CID || 0),
      }
      alert(`[FPO] Block edit success\n${JSON.stringify(blockInfo, null, 2)}`)
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

onMounted(() => {
  if (!props.editAction) {
    return
  }

  document.addEventListener('click', handleDocumentClick)
  open.value = true
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>
