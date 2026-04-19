<template>
  <label class="grid gap-2">
    <span class="label-text font-semibold text-base-content">{{ field.label }}</span>
    <textarea
      :value="normalizedValue"
      :rows="rows"
      class="textarea textarea-bordered min-h-28 w-full resize-y"
      @input="handleInput"
    />
  </label>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue: string
  field: {
    id: string
    label: string
    definition?: Record<string, unknown>
  }
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void
}>()

const normalizedValue = computed(() => props.modelValue ?? '')
const rows = computed(() => {
  const value = Number(props.field.definition?.rows ?? 4)
  return Number.isFinite(value) && value > 0 ? value : 4
})

function handleInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLTextAreaElement).value)
}
</script>
