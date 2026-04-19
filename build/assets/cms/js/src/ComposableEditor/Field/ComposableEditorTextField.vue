<template>
  <label class="grid gap-2">
    <span class="label-text font-semibold text-base-content">{{ field.label }}</span>
    <input
      :value="normalizedValue"
      type="text"
      class="input input-bordered w-full"
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

function handleInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>
