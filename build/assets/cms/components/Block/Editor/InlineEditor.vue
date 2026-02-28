<template>
  <DialogEditor
    :edit-action="editAction"
    :dialog-title="dialogTitle"
    :dialog-width="dialogWidth"
    :dialog-height="dialogHeight"
  />
</template>

<script setup lang="ts">
import DialogEditor from './DialogEditor.vue'

defineProps<{
  editAction?: string
  dialogTitle?: string
  dialogWidth?: string | number
  dialogHeight?: string | number
}>()
</script>
