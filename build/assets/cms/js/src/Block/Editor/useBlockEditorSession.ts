import { computed, ref, unref, watch, type MaybeRefOrGetter } from 'vue'
import { normalizeJsonResponse, useAjax } from '@concretecms/backendui'
import { useConcreteUiStore } from '../../../stores/concrete-ui'
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
  closeBehavior?: 'after-operation' | 'manual'
  onSuccess?: (payload: { response: any; operationId: string }) => void
}

type SessionOptions = {
  onUpdated?: (payload: { response: any }) => void
  onClosed?: () => void
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
  const uiStore = useConcreteUiStore()
  const isSubmitting = ref(false)
  const pendingOperationId = ref<string | null>(null)
  const pendingOperationResponse = ref<any | null>(null)
  const pendingOperationHasStarted = ref(false)
  const pendingOperationReachedDone = ref(false)
  const awaitingQueuedOperation = ref(false)

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

  function resetPendingOperationState() {
    pendingOperationId.value = null
    pendingOperationResponse.value = null
    pendingOperationHasStarted.value = false
    pendingOperationReachedDone.value = false
    awaitingQueuedOperation.value = false
  }

  function queueResponse(response: any): string {
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

      uiStore.enqueuePageOperation(operation)
      return operation.id
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

    uiStore.enqueuePageOperation(operation)
    return operation.id
  }

  function submit(submitRequest: BlockEditorSubmitRequest, submitOptions: SubmitOptions = {}) {
    if (isSubmitting.value || pendingOperationId.value) {
      return
    }

    const closeBehavior = submitOptions.closeBehavior ?? 'after-operation'
    const currentContext = resolvedContext.value

    isSubmitting.value = true
    awaitingQueuedOperation.value = false

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

        const operationId = queueResponse(normalizedResponse)

        if (closeBehavior === 'after-operation') {
          pendingOperationId.value = operationId
          pendingOperationResponse.value = normalizedResponse
          pendingOperationHasStarted.value = false
          pendingOperationReachedDone.value = false
          awaitingQueuedOperation.value = true
        }

        submitOptions.onSuccess?.({
          response: normalizedResponse,
          operationId,
        })
      },
      onComplete: () => {
        if (!awaitingQueuedOperation.value) {
          isSubmitting.value = false
        }
      },
    })
  }

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
          resetPendingOperationState()
          isSubmitting.value = false
          options.onUpdated?.({ response })
          options.onClosed?.()
        }
        return
      }

      if (operation.status === 'queued' || operation.status === 'running') {
        pendingOperationHasStarted.value = true
      }

      if (operation.status === 'done') {
        pendingOperationReachedDone.value = true
        const response = pendingOperationResponse.value
        resetPendingOperationState()
        isSubmitting.value = false
        options.onUpdated?.({ response })
        options.onClosed?.()
        return
      }

      if (operation.status === 'failed') {
        resetPendingOperationState()
        isSubmitting.value = false
      }
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
