<template>
  <div
      ref="rootEl"
      data-theme="light"
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

    <!-- Menu: stays absolutely positioned, with dynamic top -->
    <div
        v-if="$slots.menu"
        ref="menuEl"
        :class="[
        'absolute left-1/2 -translate-x-1/2 z-500 pointer-events-auto transition-all duration-200',
        isStoreActiveMatch ? 'opacity-100' : 'opacity-0'
      ]"
        :style="{ top: menuTop }"
    >
      <slot name="menu" />
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
import { ref, computed, watch, onMounted } from 'vue'
import { useUiStore } from '@concretecms/backendui'

const props = withDefaults(
    defineProps<{
      itemId: string
      hoverOutlineColor?: string
      activeOutlineColor?: string
      activeBgClass?: string
    }>(),
    {
      hoverOutlineColor: 'concrete-green',
      activeOutlineColor: 'concrete-green',
      activeBgClass: '',
    }
)

const uiStore = useUiStore()
const rootEl = ref<HTMLElement | null>(null)
const menuEl = ref<HTMLElement | null>(null)

const toolbarHeight = 72
const menuOffsetAbove = -60
const menuOffsetBelow = 12
const menuTop = ref(`${menuOffsetAbove}px`)

const isStoreHoverMatch = computed(() => {
  if (!uiStore.clickProxy.activeElementId) {
    return uiStore.clickProxy.hoverElementId === props.itemId
  }
})
const isVisible = computed(() => isStoreActiveMatch.value || isStoreHoverMatch.value)
const isStoreActiveMatch = computed(() => uiStore.clickProxy.activeElementId === props.itemId)

const outlineColor = computed(() => {
  if (isStoreActiveMatch.value) return props.activeOutlineColor
  if (isStoreHoverMatch.value) return props.hoverOutlineColor
  return 'outline-transparent'
})

function activateHotSpot() {
  uiStore.clickProxy.activeElementId = props.itemId
}

function updateMenuTop() {
  if (!rootEl.value || !menuEl.value || !isStoreActiveMatch.value) return

  const rootRect = rootEl.value.getBoundingClientRect()
  const desiredTop = menuOffsetAbove

  // If the menu would be too high (hidden under toolbar), adjust it down
  const projectedTop = rootRect.top + desiredTop
  if (projectedTop < toolbarHeight) {
    const adjustedTop = (toolbarHeight + menuOffsetBelow) - rootRect.top
    menuTop.value = `${adjustedTop}px`
  } else {
    menuTop.value = `${desiredTop}px`
  }
}

onMounted(updateMenuTop)
watch(() => uiStore.scroll.y, updateMenuTop)
</script>
