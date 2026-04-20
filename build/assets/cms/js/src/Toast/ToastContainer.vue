<template>
    <ToastProvider :duration="toast.activeToast?.duration ?? 3000" swipe-direction="right">
      <Toast
        v-if="toast.activeToast"
        :key="toast.activeToast.id"
        :open="open"
        :variant="toastVariant"
        @update:open="handleOpenUpdate"
      >
        <div class="grid gap-1">
          <ToastTitle>{{ toast.activeToast.title }}</ToastTitle>
          <ToastDescription>{{ toast.activeToast.message }}</ToastDescription>
        </div>
        <ToastClose />
      </Toast>
      <ToastViewport />
    </ToastProvider>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useToast } from "./toast";
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

const toastVariant = computed(() => {
  const variant = toast.activeToast?.variant
  if (variant === 'error') {
    return 'error'
  }

  return 'success'
})

function handleOpenUpdate(nextOpen: boolean) {
  open.value = nextOpen
  if (!nextOpen && toast.activeToast) {
    toast.completeToast(toast.activeToast.id)
  }
}

watch(
  () => toast.activeToast,
  (activeToast) => {
    open.value = Boolean(activeToast)
  },
  { immediate: true }
)
</script>
