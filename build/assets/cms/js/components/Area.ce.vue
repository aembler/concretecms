<template>
  <!-- In-page custom elements render without shadow DOM, so Tailwind utility classes are not available here. -->
  <!-- To edit the styles for classes used here, look for _area.scss in the cms.scss file. //-->
  <div class="concrete-area"
       @mouseover="handleAreaHover"
       @mouseout="handleAreaOut"
       :class="{
         'concrete-area-empty': totalBlocks === 0,
         'concrete-area-hover': uiStore.page.hoverArea === areaKey,
         'concrete-area-interactions-disabled': !isInteractionsEnabled
       }">
    <div class="concrete-area-inner">
      <slot />
    </div>
    <div v-show="totalBlocks === 0">
      <div class="concrete-area-empty-pill" v-if="totalBlocks === 0">Empty {{name}}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useConcreteUiStore } from '../stores/concrete-ui'
import type { AddBlockOperation, BlockRef, UpdateBlockOperation } from '../stores/types/page-operations'
import { BlockRenderer } from '../support/dom/BlockRenderer'
import { useToast } from '../utilities/toast'
import {
  normalizeJsonResponse,
  useAjax,
} from '@concretecms/backendui'

const props = withDefaults(defineProps<{
  name?: string
  totalBlocks?: number
  pageId: string | number
  areaHandle: string
}>(), {
  name: '',
  totalBlocks: 0,
})

const uiStore = useConcreteUiStore()
const blockRenderer = new BlockRenderer()
const runningUpdateOperationId = ref<string | null>(null)
const runningAddOperationId = ref<string | null>(null)
const areaKey = computed(() => `${props.pageId}:${props.areaHandle}`)
const isInteractionsEnabled = computed(() => Boolean((uiStore.page as any)?.interactionsEnabled ?? true))
const toast = useToast()
const { request } = useAjax()

const activeUpdateOperation = computed<UpdateBlockOperation | null>(() => {
  const operationId = uiStore.page.activeOperationId
  const operation = uiStore.page.operationsQueue.find(
    (item): item is UpdateBlockOperation =>
      item.id === operationId && item.type === 'block.update' && item.status === 'running'
  )
  return operation ?? null
})

const activeAddOperation = computed<AddBlockOperation | null>(() => {
  const operationId = uiStore.page.activeOperationId
  const operation = uiStore.page.operationsQueue.find(
    (item): item is AddBlockOperation =>
      item.id === operationId && item.type === 'block.add' && item.status === 'running'
  )
  return operation ?? null
})

function handleAreaHover() {
  uiStore.page.hoverArea = areaKey.value
}

function handleAreaOut() {
  if (uiStore.page.hoverArea === areaKey.value) {
    uiStore.page.hoverArea = null;
  }
}

function requestJson(url: string): Promise<any> {
  return new Promise((resolve) => {
    let didResolve = false
    request({
      url,
      method: 'GET',
      skipResponseValidation: true,
      onSuccess: (response) => {
        didResolve = true
        resolve(normalizeJsonResponse(response))
      },
      onComplete: () => {
        if (!didResolve) {
          resolve({})
        }
      },
    })
  })
}

function showSuccessToast(title: string, message: string) {
  toast.success(title, message)
}

async function runBlockUpdateOperation(operation: UpdateBlockOperation): Promise<void> {
  try {
    runningUpdateOperationId.value = operation.id
    const replacementHtml = operation.replacementHtml
      ?? await blockRenderer.fetchRenderedBlockHtml(operation.updatedBlock)

    await blockRenderer.replaceBlock({
      originalBlockId: operation.originalBlock.bID,
      replacementHtml,
      evaluateScripts: true,
    })

    showSuccessToast(
      operation.response?.title || 'Update Block',
      operation.response?.message || 'The block has been saved successfully.'
    )

    uiStore.completePageOperation(operation.id)
  } catch {
    uiStore.failPageOperation(operation.id)
  } finally {
    runningUpdateOperationId.value = null
  }
}

async function runAddBlockOperation(operation: AddBlockOperation): Promise<void> {
  try {
    runningAddOperationId.value = operation.id
    let submitResponse = operation.response || null
    if (!submitResponse?.bID) {
      const submitParams = new URLSearchParams()
      submitParams.set('cID', String(operation.target.pageId))
      submitParams.set('arHandle', String(operation.target.areaHandle))
      submitParams.set('btID', String(operation.blockTypeId))
      submitParams.set('mode', 'edit')
      submitParams.set('add', '1')
      submitParams.set('ccm_token', String((window as any).CCM_SECURITY_TOKEN || ''))
      submitParams.set('dragAreaBlockID', String(operation.target.afterBlockId || 0))

      const submitUrl = `${CCM_DISPATCHER_FILENAME}/ccm/system/dialogs/page/add_block/submit?${submitParams.toString()}`
      submitResponse = await requestJson(submitUrl) as any
    }

    const newBlock: BlockRef = {
      bID: submitResponse?.bID,
      arHandle: String(submitResponse?.arHandle || operation.target.areaHandle),
      cID: submitResponse?.cID || operation.target.pageId,
    }
    const replacementHtml = await blockRenderer.fetchRenderedBlockHtml(newBlock)
    await blockRenderer.insertBlock({
      target: operation.target,
      replacementHtml,
      evaluateScripts: true,
    })

    showSuccessToast(
      submitResponse?.title || 'Add Block',
      submitResponse?.message || 'The block has been added successfully.'
    )

    uiStore.completePageOperation(operation.id)
  } catch {
    uiStore.failPageOperation(operation.id)
  } finally {
    runningAddOperationId.value = null
  }
}

watch(
  activeUpdateOperation,
  (operation) => {
    if (!operation || `${operation.originalBlock.cID}:${operation.originalBlock.arHandle}` !== areaKey.value) {
      return
    }

    if (runningUpdateOperationId.value === operation.id) {
      return
    }

    void runBlockUpdateOperation(operation)
  },
  { immediate: true }
)

watch(
  activeAddOperation,
  (operation) => {
    if (!operation || `${operation.target.pageId}:${operation.target.areaHandle}` !== areaKey.value) {
      return
    }

    if (runningAddOperationId.value === operation.id) {
      return
    }

    void runAddBlockOperation(operation)
  },
  { immediate: true }
)

</script>
