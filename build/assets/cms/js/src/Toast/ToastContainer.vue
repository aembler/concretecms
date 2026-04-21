<template>
    <ToastProvider :duration="toastStore.activeToast?.duration ?? 3000" swipe-direction="right">
      <Toast
        v-if="toastStore.activeToast"
        :key="toastStore.activeToast.id"
        :open="open"
        :variant="toastVariant"
        @update:open="handleOpenUpdate"
      >
        <div class="grid gap-1">
          <ToastTitle>{{ toastStore.activeToast.title }}</ToastTitle>
          <ToastDescription>{{ toastStore.activeToast.message }}</ToastDescription>
        </div>
        <ToastClose />
      </Toast>
      <ToastViewport />
    </ToastProvider>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useToastStore } from "./@stores/toast";
import {
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
} from '@concretecms/backendui'

const toastStore = useToastStore()
const open = ref(false)

const toastVariant = computed(() => {
  const variant = toastStore.activeToast?.variant
  if (variant === 'error') {
    return 'error'
  }

  return 'success'
})

function handleOpenUpdate(nextOpen: boolean) {
  open.value = nextOpen
  if (!nextOpen && toastStore.activeToast) {
    toastStore.completeToast(toastStore.activeToast.id)
  }
}

watch(
  () => toastStore.activeToast,
  (activeToast) => {
    open.value = Boolean(activeToast)
  },
  { immediate: true }
)
</script>
