<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
      </DialogHeader>

      <form
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
        <button type="button" class="btn btn-secondary" @click="emit('update:open', false)">
          Cancel
        </button>
        <button type="button" data-submit="delete-block-form" class="btn btn-error" @click="submitDelete">
          Delete
        </button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, watch, ref } from 'vue'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@concretecms/backendui'
import { useConcreteUiStore } from '../../stores/concrete-ui'
import type { DeleteBlockOperation } from '../../stores/types/page-operations'

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
  pageId?: string | number
}>(), {
  defaultsMessage: '',
  isMasterCollection: false,
  dialogTitle: 'Delete',
  progressiveOperationTitle: 'Delete Blocks',
  pageId: '',
})

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
}>()

const deleteAll = ref('0')
const uiStore = useConcreteUiStore()

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
  const useDeleteAll = parseInt(deleteAll.value, 10) === 1
  const operation: DeleteBlockOperation = {
    id: `block.delete.${String(props.blockId)}.${Date.now()}`,
    type: 'block.delete',
    status: 'queued',
    pageBlock: {
      bID: props.blockId,
      arHandle: props.areaHandle,
      cID: props.pageId || '',
    },
    deleteAction: props.deleteAction,
    deleteAllAction: props.deleteAllAction,
    deleteAll: useDeleteAll,
  }

  uiStore.enqueuePageOperation(operation)
  emit('update:open', false)
}
</script>
