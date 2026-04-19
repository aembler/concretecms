<template>
  <FloatingPanelHeaderTabs
    :model-value="modelValue"
    :tabs="tabOptions"
    :show-labels="true"
    @update:model-value="emit('update:modelValue', $event)"
  />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { FloatingPanelHeaderTabs } from '@concretecms/backendui'

const props = defineProps<{
  modelValue: string | null
  tabs: Array<{
    id: string
    name: string
  }>
}>()

const emit = defineEmits<{
  (event: 'update:modelValue', value: string): void
}>()

const tabOptions = computed(() => props.tabs.map((tab) => ({
  id: tab.id,
  label: tab.name,
})))
</script>
