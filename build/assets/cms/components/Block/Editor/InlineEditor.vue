<template>
  <div>
    <div ref="inlineEditorMountTarget"></div>
    <div class="flex items-center gap-2">
      <div class="text-sm font-semibold text-slate-800">Inline Editor</div>
      <div class="ms-auto flex items-center gap-2">
        <button type="button" class="btn btn-secondary btn-sm" :disabled="isSubmitting" @click="handleCancel">Cancel</button>
        <button type="button" class="btn btn-primary btn-sm" :disabled="isSubmitting || isLoading || !isReady" @click="handleSave">
          Save
        </button>
      </div>
    </div>
    <div v-if="isLoading" class="mt-2 text-xs text-slate-600">Loading editor…</div>
    <div v-else-if="errorMessage" class="mt-2 text-xs text-error">{{ errorMessage }}</div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, useTemplateRef } from 'vue'
import { normalizeJsonResponse, useAjax } from '@concretecms/backendui'
import { useConcreteUiStore } from '../../../stores/concrete-ui'
import type { BlockRef, UpdateBlockOperation } from '../../../stores/types/page-operations'

const props = defineProps<{
  blockTypeId?: number
  blockId: string | number
  areaHandle: string
  pageId: string | number
}>()

const inlineEditorMountTarget = useTemplateRef('inlineEditorMountTarget')

const emit = defineEmits<{
  (e: 'updated', payload?: { response?: any }): void
}>()

const { request } = useAjax()
const uiStore = useConcreteUiStore()
const isLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const isReady = computed(() => !isLoading.value && errorMessage.value === '')

function getDialogUrl(): string {
  const params = new URLSearchParams({
    cID: String(props.pageId),
    arHandle: String(props.areaHandle),
    bID: String(props.blockId),
  })

  return `/ccm/system/dialogs/block/edit?${params.toString()}`
}


function hasResponseErrors(response: any): boolean {
  return Boolean(
    response?.error
    || (Array.isArray(response?.errors) && response.errors.length > 0)
  )
}




async function loadInlineForm() {
  isLoading.value = true
  errorMessage.value = ''

  const response = await fetch(getDialogUrl(), {
    method: 'GET',
    credentials: 'same-origin',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
    },
  })

  if (!response.ok) {
    errorMessage.value = `Failed to load editor form (${response.status}).`
    isLoading.value = false
    return
  }

  inlineEditorMountTarget.value.innerHTML = await response.text()
  isLoading.value = false
}

function handleSave() {
  if (isSubmitting.value || isLoading.value) {
    return
  }

  const form = findPrimaryInlineForm()
  if (!form) {
    errorMessage.value = 'Inline editor form is not available.'
    return
  }

  const url = form.getAttribute('action') || getDialogUrl()
  const formData = new FormData(form)

  isSubmitting.value = true
  errorMessage.value = ''

  request({
    url,
    method: 'POST',
    body: formData,
    skipResponseValidation: true,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)
      if (hasResponseErrors(normalizedResponse)) {
        errorMessage.value = 'The block could not be saved. Please check the form.'
        return
      }

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
    },
    onComplete: () => {
      isSubmitting.value = false
    },
  })
}

function handleCancel() {
  emit('updated')
}

onMounted(() => {
  void loadInlineForm()
})
</script>
