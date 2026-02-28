<template>
  <div class="outline-2 outline-concrete-area rounded-2 mb-8 relative">
    <div class="relative">
      <slot />
    </div>
    <div v-if="totalBlocks === 0" class="absolute inset-0 flex items-center pointer-events-none">
      <div class="mx-auto font-semibold uppercase rounded-full py-1 px-6 text-xs bg-concrete-area text-gray-400">Empty {{name}}</div>
    </div>
  </div>

  <ToastProvider :duration="3000" swipe-direction="right">
    <Toast :open="toastOpen" variant="success" @update:open="toastOpen = $event">
      <div class="grid gap-1">
        <ToastTitle>{{ toastTitle }}</ToastTitle>
        <ToastDescription>{{ toastDescription }}</ToastDescription>
      </div>
      <ToastClose />
    </Toast>
    <ToastViewport />
  </ToastProvider>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useConcreteUiStore } from '../stores/concrete-ui'
import type { UpdateBlockOperation } from '../stores/types/page-operations'
import { BlockRenderer } from '../support/dom/BlockRenderer'
import {
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
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
const areaKey = computed(() => `${props.pageId}:${props.areaHandle}`)
const toastOpen = ref(false)
const toastTitle = ref('Update Block')
const toastDescription = ref('The block has been saved successfully.')

const activeUpdateOperation = computed<UpdateBlockOperation | null>(() => {
  const operationId = uiStore.page.activeOperationId
  const operation = uiStore.page.operationsQueue.find(
    (item): item is UpdateBlockOperation =>
      item.id === operationId && item.type === 'block.update' && item.status === 'running'
  )
  return operation ?? null
})

function matchesArea(operation: UpdateBlockOperation): boolean {
  return `${operation.originalBlock.cID}:${operation.originalBlock.arHandle}` === areaKey.value
}

async function runBlockUpdateOperation(operation: UpdateBlockOperation): Promise<void> {
  runningUpdateOperationId.value = operation.id

  try {
    const replacementHtml = operation.replacementHtml
      ?? await blockRenderer.fetchRenderedBlockHtml(operation.updatedBlock)

    await blockRenderer.replaceBlock({
      originalBlockId: operation.originalBlock.bID,
      replacementHtml,
      evaluateScripts: true,
    })

    toastTitle.value = operation.response?.title || 'Update Block'
    toastDescription.value = operation.response?.message || 'The block has been saved successfully.'
    toastOpen.value = false
    toastOpen.value = true

    uiStore.completePageOperation(operation.id)
  } catch (error) {
    uiStore.failPageOperation(operation.id)
  } finally {
    runningUpdateOperationId.value = null
  }
}

watch(
  activeUpdateOperation,
  (operation) => {
    if (!operation || !matchesArea(operation)) {
      return
    }

    if (runningUpdateOperationId.value === operation.id) {
      return
    }

    void runBlockUpdateOperation(operation)
  },
  { immediate: true }
)

</script>
