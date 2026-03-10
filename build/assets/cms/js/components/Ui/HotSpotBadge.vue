<template>
  <div
    :class="badgeClass"
    :style="badgeStyles"
  >
    {{ label }}
  </div>
</template>

<script setup lang="ts">
import { computed, inject, type CSSProperties } from 'vue'
import { HOT_SPOT_BADGE_GEOMETRY_KEY, type HotSpotBadgeGeometry } from '../../support/dom/hotspot'

type HotSpotBadgeColorProps = {
  backgroundColor: string
  textColor: string
  hoverBackgroundColor?: string
  hoverTextColor?: string
  activeBackgroundColor?: string
  activeTextColor?: string
}

const props = withDefaults(defineProps<{
  label: string
  isHovered?: boolean,
  isActive?: boolean,
  badgeColor: HotSpotBadgeColorProps,
  badgePlacement?: 'offset-top-center' | 'offset-bottom-center' | 'notch-top-center' | 'block-bottom-center' | 'middle-center' | null,
  appearBehavior?: 'targeted' | 'display',
}>(), {
  isHovered: false,
  isActive: false,
  badgePlacement: null,
  appearBehavior: 'targeted',
})

const hotspotGeometry = inject(HOT_SPOT_BADGE_GEOMETRY_KEY, null) as HotSpotBadgeGeometry | null
const fallbackGeometry = {
  top: 0,
  left: 0,
  bottom: 0,
  width: 0,
}

const top = computed(() => hotspotGeometry?.top.value ?? fallbackGeometry.top)
const left = computed(() => hotspotGeometry?.left.value ?? fallbackGeometry.left)
const bottom = computed(() => hotspotGeometry?.bottom.value ?? fallbackGeometry.bottom)
const width = computed(() => hotspotGeometry?.width.value ?? fallbackGeometry.width)
const height = computed(() => Math.max(0, bottom.value - top.value))

const badgeOffsetPx = 10; // the offset from the bottom or top when using the offset placements
const badgeCenterOffsetPx = 10; // the offset used to position badge _just_ above the border.
const isTargeted = computed(() => Boolean(props.isHovered || props.isActive))
const isBadgeVisible = computed(() => props.appearBehavior === 'display' || isTargeted.value)
const isNotchTopCenter = computed(() => props.badgePlacement === 'notch-top-center')

function resolveColorState() {
  const backgroundHover = props.badgeColor.hoverBackgroundColor || props.badgeColor.backgroundColor
  const textHover = props.badgeColor.hoverTextColor || props.badgeColor.textColor
  const backgroundActive = props.badgeColor.activeBackgroundColor || backgroundHover
  const textActive = props.badgeColor.activeTextColor || textHover

  if (props.isActive) {
    return {
      backgroundColor: backgroundActive,
      color: textActive,
    }
  }

  if (props.isHovered) {
    return {
      backgroundColor: backgroundHover,
      color: textHover,
    }
  }

  return {
    backgroundColor: props.badgeColor.backgroundColor,
    color: props.badgeColor.textColor,
  }
}

function badgeLeft() {
  if (!props.badgePlacement) {
    return '0px'
  }

  if (props.badgePlacement === 'block-bottom-center') {
    return `${left.value + badgeOffsetPx}px`
  }

  if (props.badgePlacement === 'middle-center') {
    return `${left.value + (width.value / 2)}px`
  }

  return `${left.value + (width.value / 2)}px`
}

function badgeTop() {
  if (!props.badgePlacement) {
    return '0px'
  }

  if (props.badgePlacement === 'offset-top-center') {
    return `${top.value + badgeOffsetPx}px`
  }

  if (props.badgePlacement === 'notch-top-center') {
    return `${top.value - badgeCenterOffsetPx}px`
  }

  if (props.badgePlacement === 'offset-bottom-center') {
    return `${bottom.value - badgeOffsetPx}px`
  }

  if (props.badgePlacement === 'block-bottom-center') {
    return `${top.value + height.value - (badgeOffsetPx * 3.75) }px`
  }

  if (props.badgePlacement === 'middle-center') {
    return `${top.value + (height.value / 2)}px`
  }

  return `${top.value - badgeCenterOffsetPx}px`
}

function badgeTransform(): CSSProperties['transform'] {
  const centerX = 'translate3d(-50%,'

  if (!props.badgePlacement) {
    return `${centerX} 0, 0)`
  }

  if (props.badgePlacement === 'offset-top-center') {
    return `${centerX} 0, 0)`
  }

  if (props.badgePlacement === 'notch-top-center') {
    return `${centerX} 0, 0)`
  }

  if (props.badgePlacement === 'block-bottom-center') {
    return 'none'
  }

  if (props.badgePlacement === 'middle-center') {
    return 'translate3d(-50%, -50%, 0)'
  }

  return `${centerX} -100%, 0)`
}

const badgeStyles = computed<CSSProperties>(() => ({
  ...resolveColorState(),
  position: 'absolute',
  zIndex: 'var(--index-layer-hotspot-badge)',
  left: badgeLeft(),
  top: badgeTop(),
  width: props.badgePlacement === 'block-bottom-center' ? `${width.value - (badgeOffsetPx * 2)}px` : 'auto',
  transform: `${badgeTransform()} ${isNotchTopCenter.value ? `scale(${isBadgeVisible.value ? '1' : '0.95'})` : ''}`.trim(),
  pointerEvents: 'none',
}))

const badgeClass = computed(() => [
  'pointer-events-auto shadow-sm text-xs font-semibold uppercase rounded-full py-1 px-2 inline-block',
  isNotchTopCenter.value ? 'origin-center transition-all duration-200' : 'transition-opacity duration-200',
  isBadgeVisible.value ? 'opacity-100' : 'opacity-0'
])
</script>
