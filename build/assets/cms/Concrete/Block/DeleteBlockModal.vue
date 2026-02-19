<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
      </DialogHeader>

      <form
        ref="formEl"
        method="post"
        data-form="delete-block"
        :data-action-delete-all="deleteAllAction"
        :data-action="deleteAction"
        @submit.prevent
      >
        <p>{{ message }}</p>

        <template v-if="isMasterCollection">
          <div class="alert alert-danger">{{ defaultsMessage }}</div>

          <div class="form-group mt-4">
            <label class="control-label form-label">Instances on Child Pages</label>
            <div class="form-check">
              <input id="deleteAll1" v-model="deleteAll" type="radio" class="form-check-input" name="deleteAll" value="0">
              <label class="form-check-label" for="deleteAll1">Delete only unforked instances on child pages.</label>
            </div>
            <div class="form-check">
              <input id="deleteAll2" v-model="deleteAll" type="radio" class="form-check-input" name="deleteAll" value="1">
              <label class="form-check-label" for="deleteAll2">Delete even forked instances on child pages.</label>
            </div>
          </div>
        </template>

        <input type="hidden" name="bID" :value="blockId">
        <input type="hidden" name="arHandle" :value="areaHandle">
      </form>

      <DialogFooter>
        <button type="button" class="btn btn-secondary" :disabled="isSubmitting" @click="emit('update:open', false)">
          Cancel
        </button>
        <button type="button" data-submit="delete-block-form" class="btn btn-error" :disabled="isSubmitting" @click="submitDelete">
          Delete
        </button>
      </DialogFooter>
    </DialogContent>
  </Dialog>

  <ToastProvider :duration="3500" swipe-direction="right">
    <Toast
      :open="toastOpen"
      variant="success"
      @update:open="toastOpen = $event"
    >
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
import { computed, watch, ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  useAjax,
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport
} from '@concretecms/backendui'

const props = withDefaults(defineProps<{
  open: boolean
  deleteAction: string
  deleteAllAction: string
  message: string
  defaultsMessage?: string
  blockId: string | number
  areaHandle: string
  isMasterCollection?: boolean | string | number
  dialogTitle?: string
  progressiveOperationTitle?: string
}>(), {
  defaultsMessage: '',
  isMasterCollection: false,
  dialogTitle: 'Delete',
  progressiveOperationTitle: 'Delete Blocks'
})

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'deleted', response: any): void
}>()

const formEl = ref<HTMLFormElement | null>(null)
const isSubmitting = ref(false)
const deleteAll = ref('0')
const { request } = useAjax()
const toastOpen = ref(false)
const toastTitle = ref('Deleted')
const toastDescription = ref('Block deleted successfully.')

const isMasterCollection = computed(() => {
  if (typeof props.isMasterCollection === 'boolean') {
    return props.isMasterCollection
  }
  return props.isMasterCollection === '1' || props.isMasterCollection === 1 || props.isMasterCollection === 'true'
})

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    deleteAll.value = '0'
  }
})

function submitDelete() {
  if (isSubmitting.value) {
    return
  }

  let url = props.deleteAction
  let body: Record<string, any> = {}
  if (parseInt(deleteAll.value, 10) === 1) {
    url = props.deleteAllAction
    body = { deleteAll: 1 }
  }

  isSubmitting.value = true
  request({
    url,
    method: 'POST',
    body,
    onSuccess: (response) => {
      let normalizedResponse: any = response
      if (typeof normalizedResponse === 'string') {
        try {
          normalizedResponse = JSON.parse(normalizedResponse)
        } catch (error) {
          normalizedResponse = {}
        }
      }

      emit('update:open', false)
      emit('deleted', normalizedResponse)

      toastTitle.value = normalizedResponse?.title || 'Deleted'
      toastDescription.value = normalizedResponse?.message || 'Block deleted successfully.'

      toastOpen.value = false
      toastOpen.value = true
    },
    onComplete: () => {
      isSubmitting.value = false
    }
  })
}
</script>
