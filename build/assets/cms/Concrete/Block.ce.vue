<template>
  <div
      class="z-10 cursor-pointer group relative hover:outline-concrete-green outline-3 transition-all ease-out outline-transparent overflow-hidden"
      @click="isSelected = !isSelected"
  >
    <!-- Semi-transparent highlight layer -->
    <div
        class="absolute inset-0 z-10 pointer-events-auto transition-opacity duration-300"
        :class="{
        'bg-concrete-green opacity-40': isSelected,
        'opacity-0': !isSelected
      }"
    ></div>

    <!-- Click-blocking overlay -->
    <div class="absolute inset-0 cursor-pointer z-20"></div>

    <!-- Content -->
    <div class="z-0 relative">
      <slot />
    </div>

    <!-- Floating badge -->
    <div
        class="z-50 absolute top-0 left-1/2 -translate-x-1/2 -translate-y-full group-hover:translate-y-4 transition-all duration-300 ease-out shadow-sm text-xs font-semibold uppercase rounded-full py-1 px-2 inline-block bg-concrete-green"
    >
      {{ name }}
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  name: String
})

const isSelected = ref(false)
</script>
