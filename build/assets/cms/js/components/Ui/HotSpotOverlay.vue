<template>
  <div
      v-if="hasGeometry && isScrollVisible && (isHovered || isActive)"
      class="rounded-lg fixed z-(--index-layer-hotspot) pointer-events-auto transition-opacity duration-200"
      :style="overlayStyles"
  ></div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useHotSpotGeometry } from '../../support/dom/hotspot'

const props = withDefaults(defineProps<{
  element: HTMLElement | null,
  isHovered: boolean,
  isActive: boolean,
  hoverColor: string | null,
  activeColor: string | null,
  hoverOpacity: number,
  activeOpacity: number,
  cursor?: string,
  outset?: number | null,
  hideOnScroll?: boolean,
}>(), {
  element: null,
  isHovered: false,
  isActive: false,
  hoverColor: 'transparent',
  activeColor: 'transparent',
  hoverOpacity: 0,
  activeOpacity: 0,
  cursor: 'pointer',
  hideOnScroll: true,
  outset: 0,
})

const { isScrollSettled, top, left, width, height } = useHotSpotGeometry(() => props.element)
const outsetPx = computed(() => {
  if (props.outset === null || props.outset === undefined) {
    return 0
  }

  return Number.isFinite(props.outset) ? props.outset : 0
})
//const canApplyTopOutset = computed(() => top.value - outsetPx.value >= 0)
const canApplyTopOutset = ref(true)
const canApplyLeftOutset = computed(() => left.value - outsetPx.value >= 0)
const shouldApplyOutset = computed(() => canApplyTopOutset.value && canApplyLeftOutset.value)

const hasGeometry = computed(() => width.value > 0 && height.value > 0)
const isScrollVisible = computed(() => (props.hideOnScroll ? isScrollSettled.value : true))
const overlayStyles = computed(() => ({
  top: `${top.value - (shouldApplyOutset.value ? outsetPx.value : 0)}px`,
  left: `${left.value - (shouldApplyOutset.value ? outsetPx.value : 0)}px`,
  width: `${Math.max(0, width.value + (shouldApplyOutset.value ? (outsetPx.value * 2) : 0))}px`,
  height: `${Math.max(0, height.value + (shouldApplyOutset.value ? (outsetPx.value * 2) : 0))}px`,
  backgroundColor: props.isActive ? props.activeColor : props.hoverColor,
  opacity: props.isActive ? props.activeOpacity : props.hoverOpacity,
  cursor: props.cursor,
}))
</script>
