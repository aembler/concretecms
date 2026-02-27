<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import { useUiStore } from '@concretecms/backendui'

const uiStore = useUiStore()

function getFirstElementWithIdFromPath(eventPath: EventTarget[]): HTMLElement | null {
  const firstElementWithId = eventPath.find(
    (el): el is HTMLElement => el instanceof HTMLElement && Boolean(el.id)
  )

  return firstElementWithId || null
}

function handleMouseMove(event: MouseEvent) {
  const firstElementWithId = getFirstElementWithIdFromPath(event.composedPath())

  if (firstElementWithId?.id) {
    uiStore.clickProxy.hoverElementId = firstElementWithId.id
  } else {
    uiStore.clickProxy.hoverElementId = ''
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
  const activeId = uiStore.clickProxy.activeElementId

  if (activeId) {
    const activeMenuId = uiStore.clickProxy.activeElementMenuId
    const clickedInsideActive = isClickInsideActiveMenu(eventPath, eventTarget, activeMenuId)

    if (!clickedInsideActive) {
      const firstElementWithId = getFirstElementWithIdFromPath(eventPath)
      uiStore.clickProxy.hoverElementId = firstElementWithId?.id || ''
      uiStore.clickProxy.activeElementId = ''
      uiStore.clickProxy.activeElementMenuId = ''
      uiStore.clickProxy.doubleClickedElementId = ''
      event.stopPropagation()
    }
  } else if (uiStore.clickProxy.hoverElementId) {
    uiStore.clickProxy.activeElementId = uiStore.clickProxy.hoverElementId
  }
}

function handleGlobalDoubleClick(event: MouseEvent) {
  const path = event.composedPath() as HTMLElement[]
  const activeId = uiStore.clickProxy.activeElementId

  if (activeId) {
    const clickedInsideActive = path.some((el) =>
        (el as HTMLElement).id === activeId
    )
    if (clickedInsideActive) {
      uiStore.clickProxy.doubleClickedElementId = activeId
    }
  }
}

function handleScroll() {
  uiStore.updateScroll(window.scrollY)
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
