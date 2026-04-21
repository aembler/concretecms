<template>
  <!-- In-page custom elements render without shadow DOM, so Tailwind utility classes are not available here. -->
  <!-- To edit the styles for classes used here, look for _containers.scss in the cms.scss file. // -->
  <div
    ref="rootEl"
    @pointerenter="isPointerOver = true"
    @pointerleave="isPointerOver = false"
    class="concrete-container"
  >
    <slot />
    <HotSpot
        :element="rootEl"
        :is-targeted="isHovered"
        border-color="var(--color-concrete-container)"
        badge-placement="notch-top-center">
      <template #badge="{ isHovered: isBadgeHovered, badgePlacement }">
        <HotSpotBadge
            :label="containerName"
            :is-hovered="isBadgeHovered"
            :badge-placement="badgePlacement"
            :badge-color="{
            backgroundColor: 'var(--color-concrete-container)',
            textColor: 'white',
          }"
        />
      </template>

    </HotSpot>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import {usePageStore} from "../Page/@stores/page";
import {useBlocksStore} from "../Block/@stores/blocks";
import HotSpot from "../HotSpot/HotSpot.vue";
import HotSpotBadge from "../HotSpot/HotSpotBadge.vue";

const props = withDefaults(defineProps<{
  containerBlockId?: number | string,
  containerName?: string
}>(), {
  containerBlockId: '',
  containerName: 'Container'
})

const pageStore = usePageStore()
const blocksStore = useBlocksStore()
const rootEl = ref<HTMLElement | null>(null)
const isPointerOver = ref(false)
const effectiveHoveredBlockId = computed(() => pageStore.clickProxy.activeElementId || pageStore.clickProxy.hoverElementId)
const activeElementId = computed(() => pageStore.clickProxy.activeElementId)
const isInteractionsEnabled = computed(() => pageStore.interactionsEnabled)
const containerKey = computed(() =>
  props.containerBlockId ? `container:${props.containerBlockId}` : ''
)

function getHoveredElement(containerHoverId: string): HTMLElement | null {
  const blocks = Array.from(document.querySelectorAll<HTMLElement>('[data-concrete-block-id]'))
  const result = blocks.find((block) => block.getAttribute('data-concrete-block-id') === containerHoverId) || null
  return result
}

const hasHoveredBlockContainer = computed(() => {
  const activeHover = effectiveHoveredBlockId.value
  if (!activeHover) {
    return false
  }

  const paths = blocksStore.blockAreaMap[activeHover] || []
  const hasPathMatch = paths.includes(containerKey.value)

  if (!isInteractionsEnabled.value || !rootEl.value) {
    return false
  }

  const hoveredElement = getHoveredElement(activeHover)
  if (!hoveredElement) {
    return false
  }

  const containsHovered = rootEl.value.contains(hoveredElement)

  return hasPathMatch || containsHovered
})
const isHovered = computed(() =>
  isInteractionsEnabled.value && !activeElementId.value && (hasHoveredBlockContainer.value || isPointerOver.value)
)

function handleContainerBadgeClick() {
  alert(`Container badge clicked: ${props.containerName}`)
}
</script>
