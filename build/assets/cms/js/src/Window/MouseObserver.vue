<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import { useUiStore } from '@concretecms/backendui'
import { usePageStore } from "../Page/@stores/page";
import { useWindowStore } from "./@stores/window";

const uiStore = useUiStore()
const pageStore = usePageStore()
const windowStore = useWindowStore()
function getFirstElementWithConcreteBlockIdFromPath(eventPath: EventTarget[]): HTMLElement | null {
  const firstElementWithId = eventPath.find(
    (el): el is HTMLElement => el instanceof HTMLElement
      && Boolean(el.getAttribute('data-concrete-block-id'))
  )

  return firstElementWithId || null
}

function handleMouseMove(event: MouseEvent) {
  const firstElementWithId = getFirstElementWithConcreteBlockIdFromPath(event.composedPath())
  const hoveredBlockId = firstElementWithId?.getAttribute('data-concrete-block-id') || ''

  if (hoveredBlockId) {
    pageStore.clickProxy.hoverElementId = hoveredBlockId
  } else {
    pageStore.clickProxy.hoverElementId = ''
  }
}

function isClickInsideActiveMenu(eventPath: EventTarget[], target: EventTarget | null, activeMenuId: string): boolean {
  if (!activeMenuId) {
    return false
  }

  const activeMenuElFromPath = eventPath.find(
    (el): el is HTMLElement => el instanceof HTMLElement && el.id === activeMenuId
  )

  let activeMenuEl: HTMLElement | null = activeMenuElFromPath || null
  if (!activeMenuEl) {
    const activeMenuSelector = `#${CSS.escape(activeMenuId)}`
    if (typeof uiStore.menuContainer === 'string') {
      activeMenuEl = document.querySelector(activeMenuSelector)
    } else if (uiStore.menuContainer instanceof HTMLElement) {
      activeMenuEl = uiStore.menuContainer.querySelector(activeMenuSelector)
    }
  }

  if (!activeMenuEl) {
    return false
  }

  if (target instanceof Node && activeMenuEl.contains(target)) {
    return true
  }

  return eventPath.includes(activeMenuEl)
}

function handleGlobalClick(event: MouseEvent) {
  // Ignore click events that are part of a double-click sequence.
  if (event.detail > 1) {
    return
  }

  const eventPath = event.composedPath()
  const eventTarget = event.target
  const activeId = pageStore.clickProxy.activeElementId

  if (activeId) {
    const activeMenuId = pageStore.clickProxy.activeElementMenuId
    const clickedInsideActive = isClickInsideActiveMenu(eventPath, eventTarget, activeMenuId)

    if (!clickedInsideActive) {
      const firstElementWithId = getFirstElementWithConcreteBlockIdFromPath(eventPath)
      const clickedBlockId = firstElementWithId?.getAttribute('data-concrete-block-id') || ''
      pageStore.clickProxy.hoverElementId = clickedBlockId
      pageStore.clickProxy.activeElementId = ''
      pageStore.clickProxy.activeElementMenuId = ''
      pageStore.clickProxy.doubleClickedElementId = null
      event.stopPropagation()
    }
  } else if (pageStore.clickProxy.hoverElementId) {
    pageStore.clickProxy.activeElementId = pageStore.clickProxy.hoverElementId
  }
}

function handleGlobalDoubleClick(event: MouseEvent) {
  const path = event.composedPath() as HTMLElement[]
  const activeId = pageStore.clickProxy.activeElementId

  if (activeId) {
    const clickedInsideActive = path.some((el) =>
        el instanceof HTMLElement && el.getAttribute('data-concrete-block-id') === activeId
    )
    if (clickedInsideActive) {
      pageStore.setDoubleClickedElementId(activeId)
    }
  }
}

function handleScroll() {
  windowStore.update(window.scrollY)
}

onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('click', handleGlobalClick)
  document.addEventListener('dblclick', handleGlobalDoubleClick)
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('click', handleGlobalClick)
  document.removeEventListener('dblclick', handleGlobalDoubleClick)
  window.removeEventListener('scroll', handleScroll)
})
</script>

<template>
  <!-- No visual output; purely behavior-driven -->
</template>
