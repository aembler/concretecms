<template>
  <div class="outline-2 outline-concrete-area rounded-2 mb-8 relative">
    <div class="relative">
      <slot />
    </div>
    <div v-if="totalBlocks === 0" class="absolute inset-0 flex items-center pointer-events-none">
      <div class="mx-auto font-semibold uppercase rounded-full py-1 px-6 text-xs bg-concrete-area text-gray-400">Empty {{name}}</div>
    </div>
  </div>
</template>

<script setup>

const props = defineProps({
  name: String,
  totalBlocks: Number
})

</script>
