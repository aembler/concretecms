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
  badgePlacement?: 'offset-top-center' | 'offset-bottom-center' | 'top-center' | null,
}>(), {
  isHovered: false,
  isActive: false,
  badgePlacement: null,
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

const badgeOffsetPx = 20; // the offset from the bottom or top when using the offset placements
const badgeCenterOffsetPx = 8; // the offset used to position badge _just_ above the border.

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

  return `${left.value + (width.value / 2)}px`
}

function badgeTop() {
  if (!props.badgePlacement) {
    return '0px'
  }

  if (props.badgePlacement === 'offset-top-center') {
    return `${top.value + badgeOffsetPx}px`
  }

  if (props.badgePlacement === 'offset-bottom-center') {
    return `${bottom.value - badgeOffsetPx}px`
  }

  return `${top.value - badgeCenterOffsetPx}px`
}

function badgeTransform(): CSSProperties['transform'] {
  const centerX = 'translate3d(-50%,'

  if (!props.badgePlacement) {
    return `${centerX} 0, 0)`
  }

  if (props.badgePlacement === 'top-center') {
    return `${centerX} 0, 0)`
  }

  if (props.badgePlacement === 'offset-top-center') {
    return `${centerX} 0, 0)`
  }

  return `${centerX} -100%, 0)`
}

const badgeStyles = computed<CSSProperties>(() => ({
  ...resolveColorState(),
  position: 'absolute',
  zIndex: 'var(--index-layer-hotspot-badge)',
  left: badgeLeft(),
  top: badgeTop(),
  transform: badgeTransform(),
  pointerEvents: 'none',
}))

const badgeClass = computed(() => [
  'pointer-events-auto shadow-sm text-xs font-semibold uppercase rounded-full py-1 px-2 inline-block',
  'transition-opacity duration-200',
  props.isHovered ? 'opacity-100' : 'opacity-0'
])
</script>
