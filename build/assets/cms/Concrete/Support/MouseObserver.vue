<script setup lang="ts">
import { onMounted, onBeforeUnmount } from 'vue'
import { useUiStore } from '@concretecms/backendui'
import _ from 'lodash'

const uiStore = useUiStore()

function handleMouseMove(event: MouseEvent) {
  const path = event.composedPath() as HTMLElement[]
  const firstElementWithId = path.find(el => (el as HTMLElement).id) as HTMLElement | undefined

  if (firstElementWithId) {
    uiStore.clickProxy.hoverElementId = firstElementWithId.id
  } else {
    uiStore.clickProxy.hoverElementId = ''
  }
}

// Store a pending click action to allow cancellation on double-click
let pendingClick: (() => void) | null = null

const triggerSingleClick = _.debounce(() => {
  if (pendingClick) {
    pendingClick()
    pendingClick = null
  }
}, 10)

function handleGlobalClick(event: MouseEvent) {
  pendingClick = () => {
    const path = event.composedPath() as HTMLElement[]
    const activeId = uiStore.clickProxy.activeElementId

    if (activeId) {
      const clickedInsideActive = path.some((el) =>
          (el as HTMLElement).id === uiStore.clickProxy.activeElementMenuId
      )

      if (!clickedInsideActive) {
        uiStore.clickProxy.hoverElementId = ''
        uiStore.clickProxy.activeElementId = ''
        uiStore.clickProxy.doubleClickedElementId = ''
        event.stopPropagation()
      }
    } else if (uiStore.clickProxy.hoverElementId) {
      uiStore.clickProxy.activeElementId = uiStore.clickProxy.hoverElementId
    }
  }

  triggerSingleClick()
}

function handleGlobalDoubleClick(event: MouseEvent) {
  if (pendingClick) {
    pendingClick = null // cancel pending single click
  }

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
