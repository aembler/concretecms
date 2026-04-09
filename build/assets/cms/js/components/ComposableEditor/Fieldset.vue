<template>
  <fieldset class="fieldset rounded-box border border-base-300 bg-base-100/80 p-4">
    <legend v-if="legend" class="fieldset-legend px-1 text-xs uppercase tracking-[0.12em] text-base-content/60">
      {{ legend }}
    </legend>

    <div class="grid gap-4">
      <slot />
    </div>
  </fieldset>
</template>

<script setup lang="ts">
defineProps<{
  legend?: string
}>()
</script>
