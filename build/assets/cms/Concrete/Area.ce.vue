<template>
  <div class="outline-2 outline-concrete-area rounded-2 mb-8">
    <div v-if="totalBlocks === 0" class="h-14 flex items-center">
      <div class="mx-auto font-semibold uppercase rounded-full py-1 px-6 text-xs bg-concrete-area text-gray-400">Empty {{name}}</div>
    </div>
    <div v-else>
      <slot />
    </div>
  </div>
</template>

<script setup>

const props = defineProps({
  name: String,
  totalBlocks: Number
})

</script>
