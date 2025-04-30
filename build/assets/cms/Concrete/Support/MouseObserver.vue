<script setup lang="ts">
import {onMounted, onBeforeUnmount, watch} from 'vue'
import { useUiStore } from '@concretecms/backendui' // adjust path if needed

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

function handleGlobalClick(event: MouseEvent) {
  if (uiStore.clickProxy.activeElementId) {
    const path = event.composedPath() as HTMLElement[]
    const firstElementWithId = path.find(el => (el as HTMLElement).id) as HTMLElement | undefined
    if (
        (firstElementWithId && firstElementWithId.id !== uiStore.clickProxy.activeElementId) ||
        (!firstElementWithId)
    ) {
      uiStore.clickProxy.hoverElementId = ''
      uiStore.clickProxy.activeElementId = ''
      event.stopPropagation()
    }
  }
}

function handleScroll() {
  uiStore.updateScroll(window.scrollY)
}


onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('click', handleGlobalClick, true) // useCapture=true catches early
  window.addEventListener('scrollend', handleScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('click', handleGlobalClick, true)
  window.removeEventListener('scrollend', handleScroll)
})

</script>

<template>

</template>