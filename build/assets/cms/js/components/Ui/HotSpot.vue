<template>
  <Teleport :to="uiStore.menuContainer">
  <div v-if="hasGeometry && isScrollVisible" class="fixed inset-0 pointer-events-none">
      <div
        class="z-(--index-layer-hotspot) absolute border-3 rounded-sm transition-opacity duration-200 pointer-events-none"
        :style="overlayStyles"
        :class="['opacity-0', isBorderVisible ? 'opacity-100' : '']"
      ></div>

      <slot
        v-if="hasBadgeSlot"
        name="badge"
        :is-hovered="isTargeted"
        :badge-placement="badgePlacement"
      />
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import {computed, provide, ref, useSlots} from 'vue'
import { HOT_SPOT_BADGE_GEOMETRY_KEY, useHotSpotGeometry } from '../../support/dom/hotspot'
import { useUiStore } from '@concretecms/backendui'
import { useConcreteUiStore } from '../../stores/concrete-ui'

const uiStore = useUiStore()
const concreteUiStore = useConcreteUiStore()

const props = withDefaults(defineProps<{
  element: HTMLElement | null,
  borderColor: string | null,
  isTargeted: boolean | false,
  badgePlacement?: 'offset-top-center' | 'offset-bottom-center' | 'notch-top-center' | 'block-bottom-center' | 'middle-center' | null,
  borderBehavior?: 'hover' | 'display' | null,
  hideOnScroll?: boolean,
  outset?: number | null,
}>(), {
  element: null,
  borderColor: null,
  isTargeted: false,
  badgePlacement: null,
  borderBehavior: null,
  hideOnScroll: true,
  outset: 0,
})

const { isScrollSettled, top, left, bottom, width, height } = useHotSpotGeometry(() => props.element)
const isInteractionsEnabled = computed(() => Boolean(concreteUiStore.page.interactionsEnabled))

const effectiveBorderBehavior = computed(() => props.borderBehavior || 'hover')
const isBorderVisible = computed(() =>
    isInteractionsEnabled.value && (effectiveBorderBehavior.value === 'display' || props.isTargeted)
)

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
const effectiveOutset = computed(() => (shouldApplyOutset.value ? outsetPx.value : 0))
const effectiveTop = computed(() => top.value - effectiveOutset.value)
const effectiveLeft = computed(() => left.value - effectiveOutset.value)
const effectiveWidth = computed(() => Math.max(0, width.value + (effectiveOutset.value * 2)))
const effectiveHeight = computed(() => Math.max(0, height.value + (effectiveOutset.value * 2)))
const effectiveBottom = computed(() => effectiveTop.value + effectiveHeight.value)

provide(HOT_SPOT_BADGE_GEOMETRY_KEY, {
  top: effectiveTop,
  left: effectiveLeft,
  bottom: effectiveBottom,
  width: effectiveWidth,
})

const hasGeometry = computed(() => width.value > 0 && height.value > 0)
const isScrollVisible = computed(() => (props.hideOnScroll ? isScrollSettled.value : true))
const overlayStyles = computed(() => ({
  position: 'absolute',
  top: `${effectiveTop.value}px`,
  left: `${effectiveLeft.value}px`,
  width: `${effectiveWidth.value}px`,
  height: `${effectiveHeight.value}px`,
  borderColor: `${props.borderColor}`,
  opacity: isBorderVisible.value ? 1 : 0,
}))
const slots = useSlots()
const hasBadgeSlot = computed(() => Boolean(props.badgePlacement && slots.badge))
</script>
