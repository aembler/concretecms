<template>
  <Teleport :to="uiStore.menuContainer">
    <div v-if="hasGeometry" class="fixed inset-0 pointer-events-none">
      <div
        class="z-(--index-layer-hotspot) absolute pointer-events-none"
        :class="[baseClass, isHovered ?  hoverClass : '']"
        :style="overlayStyles"
      ></div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { useHotSpotGeometry } from '../../support/dom/hotspot'
import { useUiStore } from '@concretecms/backendui'

const uiStore = useUiStore();
const props = withDefaults(defineProps<{
  element: HTMLElement | null,
  baseClass: String | null,
  hoverClass: String | null,
  isHovered: Boolean | false,
}>(), {
  element: null,
  hoverClass: null,
  baseClass: null,
  isHovered: false,
})

const { top, left, right, bottom, pageTop, pageLeft, width, height } = useHotSpotGeometry(() => props.element)
const hasGeometry = computed(() => width.value > 0 && height.value > 0)

const overlayStyles = computed(() => ({
  position: 'absolute',
  top: `${top.value}px`,
  left: `${left.value}px`,
  width: `${width.value}px`,
  height: `${height.value}px`,
}))
</script>
