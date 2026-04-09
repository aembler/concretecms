<template>
  <label class="grid gap-2">
    <span class="label-text font-semibold text-base-content">{{ field.label }}</span>
    <div class="grid grid-cols-[3.5rem_minmax(0,1fr)] items-center gap-3">
      <input
        :value="normalizedColor"
        type="color"
        class="h-11 w-14 rounded-box border border-base-300 bg-base-100 p-1"
        @input="handleColorInput"
      />
      <input
        :value="normalizedColor"
        type="text"
        class="input input-bordered w-full"
        spellcheck="false"
        @input="handleTextInput"
      />
    </div>
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

const normalizedColor = computed(() => {
  const value = String(props.modelValue ?? '').trim()
  return /^#[0-9a-fA-F]{6}$/.test(value) ? value : '#000000'
})

function handleColorInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLInputElement).value.toUpperCase())
}

function handleTextInput(event: Event) {
  emit('update:modelValue', (event.target as HTMLInputElement).value)
}
</script>
