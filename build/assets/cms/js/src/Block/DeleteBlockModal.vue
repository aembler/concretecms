<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ dialogTitle }}</DialogTitle>
      </DialogHeader>

      <form method="post" data-form="delete-block" @submit.prevent>
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
import { useBlocksStore } from "./@stores/blocks";
import type { DeleteBlockOperation } from './types'

const props = withDefaults(defineProps<{
  open: boolean
  blockId: string | number
  areaHandle: string
  isMasterCollection?: boolean
  pageId: string | number
  lang?: {
    dialogTitle?: string
    dialogMessage?: string
    defaultsDialogMessage?: string
  } | null
}>(), {
  isMasterCollection: false,
  lang: null,
})

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
}>()

const deleteAll = ref('0')
const dialogTitle = computed(() => props.lang?.dialogTitle || 'Delete Block')
const message = computed(() => props.lang?.dialogMessage || 'Are you sure you want to remove this block?')
const defaultsMessage = computed(() => (
  props.lang?.defaultsDialogMessage
  || 'Warning! This block is contained in the page type defaults. Any blocks aliased from this block in the site will be deleted. This cannot be undone.'
))

const isMasterCollection = computed(() => props.isMasterCollection === true)
const blocksStore = useBlocksStore()

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
      cID: props.pageId,
    },
    deleteAll: useDeleteAll,
  }

  blocksStore.enqueueOperation(operation)
  emit('update:open', false)
}
</script>
