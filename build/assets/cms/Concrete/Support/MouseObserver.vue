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
  const path = event.composedPath() as HTMLElement[]
  if (uiStore.clickProxy.activeElementId) {
    let foundActive = false
    path.forEach((element) => {
      if (element.id && (
          element.id === uiStore.clickProxy.activeElementMenuId
      )) {
        foundActive = true
      }
    })

    if (!foundActive) {
      uiStore.clickProxy.hoverElementId = ''
      uiStore.clickProxy.activeElementId = ''
      event.stopPropagation()
    }
  } else if (uiStore.clickProxy.hoverElementId) {
    uiStore.clickProxy.activeElementId = uiStore.clickProxy.hoverElementId
  }
}

function handleScroll() {
  uiStore.updateScroll(window.scrollY)
}


onMounted(() => {
  window.addEventListener('mousemove', handleMouseMove)
  document.addEventListener('click', handleGlobalClick) // useCapture=true catches early
  window.addEventListener('scroll', handleScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('mousemove', handleMouseMove)
  document.removeEventListener('click', handleGlobalClick)
  window.removeEventListener('scroll', handleScroll)
})

</script>

<template>

</template>