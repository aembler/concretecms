<template>
  <Teleport :to="toast.toastContainer ?? 'body'">
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
import { useToast } from "../../utilities/toast";
import {
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
} from '@concretecms/backendui'

const toast = useToast()
const open = ref(false)

const activeToast = computed(() => {
  const activeId = toast.activeToastId
  if (!activeId) {
    return null
  }

  const operation = toast.queue.find((item) => item.id === activeId)
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
    toast.finishToast(activeToast.value.id, 'done')
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
  () => [toast.queue.length, toast.activeToastId] as const,
  () => {
    if (toast.activeToastId && !activeToast.value) {
      toast.activeToastId = null
    }

    if (!toast.activeToastId) {
      toast.startNextToast()
    }
  },
  { immediate: true }
)

onMounted(() => {
  if (!toast.activeToastId) {
    toast.startNextToast()
  }
})
</script>
