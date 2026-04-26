import { computed, ref, unref, watch, type MaybeRefOrGetter } from 'vue'
import { normalizeJsonResponse, useAjax } from '@concretecms/backendui'
import {useBlocksStore} from "../@stores/blocks";
import type { AddBlockOperation, BlockRef, UpdateBlockOperation } from '../types'
import type { BlockEditorContext } from './types'

type RequestMethod = 'GET' | 'POST' | 'PUT' | 'DELETE'

type BlockEditorSubmitRequest = {
  url: string
  method?: RequestMethod
  body?: BodyInit | FormData | Record<string, unknown> | null
  skipResponseValidation?: boolean
}

type SubmitOptions = {
  responseHasErrors?: (response: any) => boolean
}

type SessionOptions = {
  onApplied?: (payload: { response: any; operationId: string }) => void
  onFailed?: (payload: { operationId: string | null }) => void
}

function applyAddRequestDefaults(
  context: BlockEditorContext,
  body?: BlockEditorSubmitRequest['body'],
): BlockEditorSubmitRequest['body'] {
  if (context.mode !== 'add') {
    return body
  }

  const nextBody = body instanceof FormData ? body : new FormData()
  nextBody.set('ccm_token', String((window as any).CCM_SECURITY_TOKEN || ''))
  nextBody.set('dragAreaBlockID', String(context.operation.addTarget.afterBlockId || 0))

  if (body && !(body instanceof FormData) && typeof body === 'object') {
    for (const [key, value] of Object.entries(body)) {
      nextBody.set(key, String(value ?? ''))
    }
  }

  return nextBody
}

export function useBlockEditorSession(
  context: MaybeRefOrGetter<BlockEditorContext>,
  options: SessionOptions = {},
) {
  const { request } = useAjax()
  const blocksStore = useBlocksStore()
  const isSubmitting = ref(false)
  const submittedOperationId = ref<string | null>(null)
  const submittedOperationResponse = ref<any | null>(null)

  const resolvedContext = computed(() => unref(context))
  const isAddMode = computed(() => resolvedContext.value.mode === 'add')

  const requestUrl = computed(() => {
    const ctx = resolvedContext.value
    const params = new URLSearchParams({
      cID: String(ctx.pageId),
      arHandle: String(ctx.areaHandle),
    })

    if (ctx.mode === 'add') {
      params.set('btID', String(ctx.operation.blockTypeId || 0))
      return `/ccm/system/dialogs/page/add_block?${params.toString()}`
    }

    params.set('bID', String(ctx.operation.blockId))
    return `/ccm/system/dialogs/block/edit?${params.toString()}`
  })

  const submitUrl = computed(() => {
    const url = new URL(requestUrl.value, window.location.origin)
    url.pathname = `${url.pathname.replace(/\/$/, '')}/submit`
    return `${url.pathname}${url.search}`
  })

  function resetSubmittedOperationState() {
    submittedOperationId.value = null
    submittedOperationResponse.value = null
  }

  function queueResponse(response: any) {
    const ctx = resolvedContext.value

    if (ctx.mode === 'add') {
      const operation: AddBlockOperation = {
        id: `block.add.${String(ctx.operation.blockTypeId || 0)}.${Date.now()}`,
        type: 'block.add',
        status: 'queued',
        blockTypeId: Number(ctx.operation.blockTypeId || 0),
        ignoreContainer: Boolean(ctx.operation.ignoreContainer ?? false),
        target: ctx.operation.addTarget,
        response,
      }

      return blocksStore.enqueueOperation(operation)
    }

    const originalBlock: BlockRef = {
      bID: ctx.operation.blockId,
      arHandle: ctx.areaHandle,
      cID: ctx.pageId,
    }
    const updatedBlock: BlockRef = {
      bID: response?.bID || originalBlock.bID,
      arHandle: response?.arHandle || originalBlock.arHandle,
      cID: response?.cID || originalBlock.cID,
    }

    const operation: UpdateBlockOperation = {
      id: `block.update.${String(originalBlock.bID)}.${Date.now()}`,
      type: 'block.update',
      status: 'queued',
      originalBlock,
      updatedBlock,
      replacementHtml: typeof response?.html === 'string' ? response.html : undefined,
      response,
    }

    return blocksStore.enqueueOperation(operation)
  }

  function submit(submitRequest: BlockEditorSubmitRequest, submitOptions: SubmitOptions = {}) {
    if (isSubmitting.value || submittedOperationId.value) {
      return
    }

    const currentContext = resolvedContext.value

    isSubmitting.value = true

    request({
      url: submitRequest.url,
      method: submitRequest.method ?? 'POST',
      body: applyAddRequestDefaults(currentContext, submitRequest.body),
      skipResponseValidation: submitRequest.skipResponseValidation ?? true,
      onSuccess: (response) => {
        const normalizedResponse: any = normalizeJsonResponse(response)

        if (submitOptions.responseHasErrors?.(normalizedResponse)) {
          return
        }

        const operation = queueResponse(normalizedResponse)
        submittedOperationId.value = operation.id
        submittedOperationResponse.value = normalizedResponse
      },
      onComplete: () => {
        if (!submittedOperationId.value) {
          isSubmitting.value = false
        }
      },
    })
  }

  const submittedOperation = computed(() =>
    blocksStore.findOperation(submittedOperationId.value)
  )

  watch(
    () => [submittedOperationId.value, submittedOperation.value?.status ?? null] as const,
    () => {
      const operationId = submittedOperationId.value
      if (!operationId) {
        return
      }

      const activeOperation = submittedOperation.value

      if (activeOperation?.status === 'failed') {
        resetSubmittedOperationState()
        isSubmitting.value = false
        options.onFailed?.({ operationId })
        return
      }

      if (activeOperation) {
        return
      }

      const response = submittedOperationResponse.value
      resetSubmittedOperationState()
      isSubmitting.value = false
      options.onApplied?.({ response, operationId })
    }
  )

  return {
    isSubmitting,
    isAddMode,
    requestUrl,
    submitUrl,
    submit,
  }
}
