<template>
  <HotSpot
      :item-id="id"
      hover-outline-color="outline-concrete-green"
      active-outline-color="outline-concrete-green"
      active-bg-class="bg-concrete-green/30"
  >
    <template #badge>
      {{ name }}
    </template>
    <slot />
  </HotSpot>
</template>

<script setup>
import HotSpot from "./Ui/HotSpot.vue"

const props = defineProps({
  id: String,
  name: String
})
</script>
