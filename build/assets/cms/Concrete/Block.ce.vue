<template>
  <HotSpot
      :item-id="id"
      :menu-id="menuId"
      hover-outline-color="outline-concrete-green"
      active-outline-color="outline-concrete-green"
      active-bg-class="bg-concrete-green/30"
  >
    <template #badge>
      {{ name }}
    </template>
    <template #menu>
      <Menu
          :variants="parsedVariants"
          :selected-variant="selectedVariant"
          :id="menuId">
      </Menu>
    </template>
    <slot />
  </HotSpot>
</template>

<script setup lang="ts">
import { computed } from "vue"
import HotSpot from "./Ui/HotSpot.vue"
import Menu from "./Block/Menu.vue";
import { useParsedJsonProp } from '@concretecms/backendui'

const props = defineProps({
  id: String,
  name: String,
  variants: String | Array<{ file: String; name: String }>,
  selectedVariant: String
})

const parsedVariants = useParsedJsonProp(props.variants)

let menuId = computed(() => props.id + '-menu')
</script>
