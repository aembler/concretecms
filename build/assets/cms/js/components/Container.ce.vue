<template>
  <!-- In-page custom elements render without shadow DOM, so Tailwind utility classes are not available here. -->
  <!-- To edit the styles for classes used here, look for _containers.scss in the cms.scss file. // -->
  <div
    ref="rootEl"
    :class="[
      'concrete-container',
      isHovered ? 'concrete-container-hover' : ''
    ]"
  >
    <slot />
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useConcreteUiStore } from '../stores/concrete-ui'

const props = withDefaults(defineProps<{
  containerBlockId?: number | string
}>(), {
  containerBlockId: '',
})

const uiStore = useConcreteUiStore()
const rootEl = ref<HTMLElement | null>(null)
const effectiveHoveredBlockId = computed(() => uiStore.clickProxy.activeElementId || uiStore.clickProxy.hoverElementId)
const isInteractionsEnabled = computed(() => Boolean((uiStore.page as any)?.interactionsEnabled ?? true))
const containerKey = computed(() =>
  props.containerBlockId ? `container:${props.containerBlockId}` : ''
)
const hasHoveredBlockContainer = computed(() => {
  const activeHover = effectiveHoveredBlockId.value
  if (!activeHover) {
    return false
  }

  const paths = uiStore.blockAreaMap[activeHover] || []
  if (paths.length > 0 && containerKey.value) {
    return paths.includes(containerKey.value)
  }

  if (!isInteractionsEnabled.value || !rootEl.value) {
    return false
  }

  const hoveredElement = document.getElementById(activeHover)
  if (!hoveredElement) {
    return false
  }

  return rootEl.value.contains(hoveredElement)
})
const isHovered = computed(() => isInteractionsEnabled.value && hasHoveredBlockContainer.value)
</script>
