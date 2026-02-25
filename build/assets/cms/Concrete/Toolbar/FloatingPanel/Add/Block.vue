<script setup lang="ts">
import type { Component } from 'vue'

const props = withDefaults(defineProps<{
  icon: Component
  title: string
  description?: string
  expanded?: boolean
}>(), {
  description: '',
  expanded: false,
})
</script>

<template>
  <button
    type="button"
    class="rounded-xl border border-slate-200 bg-white transition hover:border-primary/40 hover:bg-base-100"
    :class="props.expanded
      ? 'w-full px-3 py-3 text-left'
      : 'flex aspect-square w-full flex-col items-center justify-center gap-2 px-3 py-3 text-center'"
  >
    <template v-if="props.expanded">
      <div class="flex items-start gap-3">
        <component :is="props.icon" class="h-5 w-5 text-slate-500" />
        <div class="min-w-0">
          <div class="truncate text-sm font-semibold text-slate-800">{{ props.title }}</div>
          <div class="mt-1 text-xs text-slate-500 line-clamp-2">
            {{ props.description || 'Drag into an editable area to add this block.' }}
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <component :is="props.icon" class="h-5 w-5 text-slate-500" />
      <div class="line-clamp-2 text-xs font-semibold leading-tight text-slate-800">{{ props.title }}</div>
    </template>
  </button>
</template>
