<template>
  <Teleport :to="uiStore.menuContainer">
    <div v-if="hasGeometry" class="fixed inset-0 pointer-events-none">
      <div
        class="z-(--index-layer-hotspot) absolute pointer-events-none"
        :class="['border-3 transition-opacity duration-200 opacity-0', isHovered ?  'opacity-100' : '']"
        :style="overlayStyles"
      ></div>

      <slot
        v-if="hasBadgeSlot"
        name="badge"
        :is-hovered="isHovered"
        :badge-placement="badgePlacement"
      />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { computed, provide, useSlots } from 'vue'
import { HOT_SPOT_BADGE_GEOMETRY_KEY, useHotSpotGeometry } from '../../support/dom/hotspot'
import { useUiStore } from '@concretecms/backendui'

const uiStore = useUiStore()
const props = withDefaults(defineProps<{
  element: HTMLElement | null,
  borderColor: string | null,
  isHovered: boolean | false,
  badgePlacement?: 'offset-top-center' | 'offset-bottom-center' | 'top-center' | null,
  inset?: number | null,
}>(), {
  element: null,
  borderColor: null,
  isHovered: false,
  badgePlacement: null,
  inset: 0,
})

const { top, left, bottom, width, height } = useHotSpotGeometry(() => props.element)

provide(HOT_SPOT_BADGE_GEOMETRY_KEY, {
  top,
  left,
  bottom,
  width,
})

const insetPx = computed(() => {
  if (props.inset === null || props.inset === undefined) {
    return 0
  }

  return Number.isFinite(props.inset) ? props.inset : 0
})
const hasGeometry = computed(() => width.value > 0 && height.value > 0)
const overlayStyles = computed(() => ({
  position: 'absolute',
  top: `${top.value + insetPx.value}px`,
  left: `${left.value + insetPx.value}px`,
  width: `${Math.max(0, width.value - (insetPx.value * 2))}px`,
  height: `${Math.max(0, height.value - (insetPx.value * 2))}px`,
  borderColor: `${props.borderColor}`,
}))

const slots = useSlots()
const hasBadgeSlot = computed(() => Boolean(props.badgePlacement && slots.badge))
</script>
