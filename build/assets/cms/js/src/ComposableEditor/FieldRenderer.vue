<template>
  <component
    v-if="component"
    :is="component"
    :field="field"
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
  />
  <div v-else class="alert alert-warning text-sm">
    No editor component is registered for field type <code>{{ field.type }}</code>.
  </div>
</template>

<script setup lang="ts">
defineProps<{
  component: any
  modelValue: string
  field: {
    id: string
    type: string
    label: string
    definition?: Record<string, unknown>
  }
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void
}>()
</script>
