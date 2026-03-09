<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import { useUiStore } from '@concretecms/backendui'
import { useConcreteUiStore } from '../../stores/concrete-ui'

const uiStore = useUiStore()
const concreteUiStore = useConcreteUiStore()

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
    concreteUiStore.clickProxy.hoverElementId = hoveredBlockId
  } else {
    concreteUiStore.clickProxy.hoverElementId = ''
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
  const activeId = concreteUiStore.clickProxy.activeElementId

  if (activeId) {
    const activeMenuId = concreteUiStore.clickProxy.activeElementMenuId
    const clickedInsideActive = isClickInsideActiveMenu(eventPath, eventTarget, activeMenuId)

    if (!clickedInsideActive) {
      const firstElementWithId = getFirstElementWithConcreteBlockIdFromPath(eventPath)
      const clickedBlockId = firstElementWithId?.getAttribute('data-concrete-block-id') || ''
      concreteUiStore.clickProxy.hoverElementId = clickedBlockId
      concreteUiStore.clickProxy.activeElementId = ''
      concreteUiStore.clickProxy.activeElementMenuId = ''
      concreteUiStore.clearDoubleClickedElementId()
      event.stopPropagation()
    }
  } else if (concreteUiStore.clickProxy.hoverElementId) {
    concreteUiStore.clickProxy.activeElementId = concreteUiStore.clickProxy.hoverElementId
  }
}

function handleGlobalDoubleClick(event: MouseEvent) {
  const path = event.composedPath() as HTMLElement[]
  const activeId = concreteUiStore.clickProxy.activeElementId

  if (activeId) {
    const clickedInsideActive = path.some((el) =>
        el instanceof HTMLElement && el.getAttribute('data-concrete-block-id') === activeId
    )
    if (clickedInsideActive) {
      concreteUiStore.setDoubleClickedElementId(activeId)
    }
  }
}

function handleScroll() {
  concreteUiStore.updateScroll(window.scrollY)
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
