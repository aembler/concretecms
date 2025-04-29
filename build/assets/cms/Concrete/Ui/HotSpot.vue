<template>
  <div
      @click="activateHotSpot"
      :class="[
      'select-none z-10 relative cursor-pointer outline-3 transition-all duration-200',
      outlineColor,
    ]"
  >
    <!-- Optional floating badge -->
    <div
        v-if="$slots.badge"
        :class="[
        'absolute top-0 left-1/2 -translate-x-1/2 pointer-events-none',
        isVisible ? 'animate-hotSpotBadge' : 'opacity-0',
        'z-50 shadow-sm text-xs font-semibold uppercase rounded-full py-1 px-2 inline-block bg-concrete-green'
      ]"
    >
      <slot name="badge" />
    </div>

    <slot />
    <div
        :class="[
        'absolute inset-0 z-10 pointer-events-auto transition-all duration-200',
        isStoreActiveMatch && activeBgClass
      ]"
    ></div>
    <div class="absolute inset-0 cursor-pointer z-20"></div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useUiStore } from '@concretecms/backendui'

const props = withDefaults(
    defineProps<{
      itemId: string
      hoverOutlineColor?: string // just the color name, like 'concrete-green'
      activeOutlineColor?: string // just the color name
      activeBgClass?: string // full class like 'bg-concrete-green/30'
    }>(),
    {
      hoverOutlineColor: 'concrete-green',
      activeOutlineColor: 'concrete-green',
      activeBgClass: '', // optional
    }
)

const uiStore = useUiStore()

const isStoreHoverMatch = computed(() => {
  if (!uiStore.clickProxy.activeElementId) {
    return uiStore.clickProxy.hoverElementId === props.itemId
  }
})

const isVisible = computed(() => {
  return isStoreActiveMatch.value || isStoreHoverMatch.value
})

const isStoreActiveMatch = computed(() => {
  return uiStore.clickProxy.activeElementId === props.itemId
})

const outlineColor = computed(() => {
  if (isStoreActiveMatch.value) {
    return props.activeOutlineColor
  }
  if (isStoreHoverMatch.value) {
    return props.hoverOutlineColor
  }
  return 'outline-transparent'
})

function activateHotSpot() {
  uiStore.clickProxy.activeElementId = props.itemId
}
</script>