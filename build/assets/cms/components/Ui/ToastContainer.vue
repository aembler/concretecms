<template>
  <Teleport :to="concreteUi.toastContainer ?? 'body'">
    <ToastProvider :duration="activeToast?.duration ?? 3000" swipe-direction="right">
      <Toast
        v-if="activeToast"
        :key="activeToast.id"
        :open="open"
        :variant="toastVariant"
        @update:open="handleOpenUpdate"
      >
        <div class="grid gap-1">
          <ToastTitle>{{ activeToast.title }}</ToastTitle>
          <ToastDescription>{{ activeToast.message }}</ToastDescription>
        </div>
        <ToastClose />
      </Toast>
      <ToastViewport />
    </ToastProvider>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useConcreteUiStore } from '../../stores/concrete-ui'
import {
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
} from '@concretecms/backendui'

const concreteUi = useConcreteUiStore()
const open = ref(false)

const activeToast = computed(() => {
  const activeId = concreteUi.page.activeToastId
  if (!activeId) {
    return null
  }

  const operation = concreteUi.page.toastQueue.find((item) => item.id === activeId)
  if (!operation) {
    return null
  }

  return operation
})

const toastVariant = computed(() => {
  const variant = activeToast.value?.variant
  if (variant === 'error') {
    return 'error'
  }

  return 'success'
})

function handleOpenUpdate(nextOpen: boolean) {
  open.value = nextOpen
  if (!nextOpen && activeToast.value) {
    concreteUi.completeToastOperation(activeToast.value.id)
  }
}

watch(
  activeToast,
  (toast) => {
    open.value = Boolean(toast)
  },
  { immediate: true }
)

watch(
  () => [concreteUi.page.toastQueue.length, concreteUi.page.activeToastId] as const,
  () => {
    if (concreteUi.page.activeToastId && !activeToast.value) {
      concreteUi.page.activeToastId = null
    }

    if (!concreteUi.page.activeToastId) {
      concreteUi.startNextToastOperation()
    }
  },
  { immediate: true }
)

onMounted(() => {
  if (!concreteUi.page.activeToastId) {
    concreteUi.startNextToastOperation()
  }
})
</script>
