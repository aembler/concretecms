<template>
  <Dialog v-if="contentReady" v-model:open="open">
    <DialogContent :style="dialogStyle" class="max-w-none flex max-h-[90vh] flex-col overflow-visible">
      <DialogHeader>
        <div class="flex w-full min-w-0 items-center gap-2">
          <DialogTitle>{{ dialogTitle || 'Edit Block' }}</DialogTitle>
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
      <div class="min-h-0 flex-1 overflow-auto">
        <div ref="contentEl" />
      </div>
      <DialogFooter>
        <button type="button" class="btn btn-secondary me-auto" @click="handleCancel">Cancel</button>
        <button type="button" class="btn btn-primary" @click="handleSave">Save</button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
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
const contentReady = ref(false)
const contentEl = ref<HTMLElement | null>(null)
const helpTooltipText = ref('')
const helpTooltipOpen = ref(false)
const { request } = useAjax()

const dialogStyle = computed(() => ({
  width: normalizeDimension(props.dialogWidth),
  height: normalizeDimension(props.dialogHeight),
  maxWidth: '95vw',
  maxHeight: '90vh',
}))

function normalizeDimension(value: string | number): string {
  if (typeof value === 'number') {
    return `${value}px`
  }

  const trimmed = String(value || '').trim()
  if (!trimmed) {
    return 'auto'
  }

  if (trimmed === 'auto') {
    return 'auto'
  }

  if (/^\d+$/.test(trimmed)) {
    return `${trimmed}px`
  }

  return trimmed
}

function renderDialogHtml(rawHtml: string) {
  if (!contentEl.value) {
    return
  }

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

  const scripts = Array.from(parser.querySelectorAll('script'))
  scripts.forEach((script) => script.remove())

  contentEl.value.innerHTML = parser.innerHTML

  // Re-execute script tags returned by legacy dialog endpoints.
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

function toggleHelpTooltip() {
  helpTooltipOpen.value = !helpTooltipOpen.value
}

function handleDocumentClick() {
  helpTooltipOpen.value = false
}

function findPrimaryForm(): HTMLFormElement | null {
  if (!contentEl.value) {
    return null
  }

  const blockForm = contentEl.value.querySelector('#ccm-block-form')
  if (blockForm instanceof HTMLFormElement) {
    return blockForm
  }

  const dialogForm = contentEl.value.querySelector('[data-dialog-form]')
  if (dialogForm instanceof HTMLFormElement) {
    return dialogForm
  }

  const firstForm = contentEl.value.querySelector('form')
  return firstForm instanceof HTMLFormElement ? firstForm : null
}

function handleSave() {
  const form = findPrimaryForm()
  if (!form) {
    return
  }

  if (typeof form.requestSubmit === 'function') {
    form.requestSubmit()
    return
  }

  const submitEvent = new Event('submit', { bubbles: true, cancelable: true })
  const wasNotCancelled = form.dispatchEvent(submitEvent)
  if (wasNotCancelled) {
    form.submit()
  }
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

  request({
    url: props.editAction,
    method: 'GET',
    skipResponseValidation: true,
    onSuccess: async (html: string) => {
      contentReady.value = true
      open.value = true
      await nextTick()
      renderDialogHtml(String(html || ''))
    }
  })
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>
