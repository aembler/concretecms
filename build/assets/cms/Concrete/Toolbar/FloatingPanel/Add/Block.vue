<script setup lang="ts">
import interact from 'interactjs'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useFloatingPanelsStore, useUiStore } from '@concretecms/backendui'

type PanelIcon = {
  type: string
  src?: string
  alt?: string
  className?: string
  svg?: string
}

const props = withDefaults(defineProps<{
  icon?: PanelIcon
  title: string
  description?: string
  expanded?: boolean
  blockTypeId?: number
  blockTypeHandle?: string
}>(), {
  description: '',
  expanded: false,
  blockTypeId: 0,
  blockTypeHandle: '',
})

const iconType = computed(() => props.icon?.type ?? '')
const imageIconSrc = computed(() => (iconType.value === 'image-file' ? props.icon?.src : ''))
const fontAwesomeClassName = computed(() => (iconType.value === 'font-awesome' ? props.icon?.className ?? '' : ''))
const inlineSvg = computed(() => (iconType.value === 'inline-svg' ? props.icon?.svg ?? '' : ''))
const uiStore = useUiStore()
const floatingPanels = useFloatingPanelsStore()
const addPanelId = 'toolbar:add'
const blockButton = ref<HTMLButtonElement | null>(null)
const highlightedDropArea = ref<HTMLElement | null>(null)
let interactable: any = null
let dragPreview: HTMLElement | null = null
let dragPreviewContainer: HTMLElement | null = null
let dragPanelBounds: DOMRect | null = null
let hasExitedAddPanel = false

function getClientCoordinates(event: any): { x: number; y: number } {
  return {
    x: Number(event?.clientX ?? event?.client?.x ?? 0),
    y: Number(event?.clientY ?? event?.client?.y ?? 0),
  }
}

function setDropHighlight(target: HTMLElement | null) {
  if (highlightedDropArea.value === target) {
    return
  }

  highlightedDropArea.value?.classList.remove('ccm-add-block-drop-target')
  highlightedDropArea.value = target
  highlightedDropArea.value?.classList.add('ccm-add-block-drop-target')
}

function getAreaElementFromPoint(x: number, y: number): HTMLElement | null {
  const target = document.elementFromPoint(x, y)
  if (!(target instanceof Element)) {
    return null
  }

  return target.closest('[data-area-handle], concrete-area, .ccm-area') as HTMLElement | null
}

function getAreaHandleFromElement(areaElement: HTMLElement | null): string {
  if (!areaElement) {
    return ''
  }

  const areaHandleFromData = areaElement.getAttribute('data-area-handle') || areaElement.getAttribute('area-handle')
  if (areaHandleFromData) {
    return areaHandleFromData
  }

  const nestedBlock = areaElement.querySelector('concrete-block[delete-area-handle]')
  if (nestedBlock instanceof HTMLElement) {
    return nestedBlock.getAttribute('delete-area-handle') ?? ''
  }

  return ''
}

function createDragPreview(source: HTMLElement, x: number, y: number) {
  const width = source.getBoundingClientRect().width
  const preview = source.cloneNode(true) as HTMLElement
  preview.style.position = 'fixed'
  preview.style.pointerEvents = 'none'
  preview.style.top = '0'
  preview.style.left = '0'
  preview.style.width = `${width}px`
  preview.style.zIndex = '2147483646'
  preview.style.opacity = '0.92'
  preview.style.boxShadow = '0 18px 30px rgba(15, 23, 42, 0.2)'
  preview.style.transform = `translate3d(${x + 12}px, ${y + 12}px, 0)`
  preview.style.willChange = 'transform'
  preview.style.transition = 'none'
  preview.style.animation = 'none'
  preview.classList.add('ccm-add-block-drag-preview')
  const menuContainer = uiStore.menuContainer
  if (menuContainer instanceof HTMLElement) {
    dragPreviewContainer = menuContainer
  } else if (typeof menuContainer === 'string' && menuContainer.trim().length > 0) {
    const resolvedContainer = document.querySelector(menuContainer)
    dragPreviewContainer = resolvedContainer instanceof HTMLElement ? resolvedContainer : document.body
  } else {
    dragPreviewContainer = document.body
  }

  dragPreviewContainer.appendChild(preview)
  dragPreview = preview
}

function moveDragPreview(x: number, y: number) {
  if (!dragPreview) {
    return
  }

  dragPreview.style.transform = `translate3d(${x + 12}px, ${y + 12}px, 0)`
}

function removeDragPreview() {
  if (!dragPreview) {
    return
  }

  dragPreview.remove()
  dragPreview = null
  dragPreviewContainer = null
}

function setAddContentDragActive(next: boolean) {
  uiStore.page.addContentDragActive = next
}

function isPointOutsideRect(x: number, y: number, rect: DOMRect): boolean {
  return x < rect.left || x > rect.right || y < rect.top || y > rect.bottom
}

onMounted(() => {
  if (!blockButton.value) {
    return
  }

  interactable = interact(blockButton.value).draggable({
    inertia: {
      resistance: 20,
      minSpeed: 180,
      endSpeed: 45,
      allowResume: true,
      smoothEndDuration: 280,
    },
    listeners: {
      start: (event: any) => {
        const target = event.currentTarget as HTMLElement
        const { x, y } = getClientCoordinates(event)
        const addPanel = target.closest('[data-add-floating-panel]')

        dragPanelBounds = addPanel instanceof HTMLElement ? addPanel.getBoundingClientRect() : null
        hasExitedAddPanel = false
        setAddContentDragActive(false)
        target.classList.add('opacity-60')
        createDragPreview(target, x, y)
      },
      move: (event: any) => {
        const { x, y } = getClientCoordinates(event)
        moveDragPreview(x, y)

        if (!hasExitedAddPanel && (!dragPanelBounds || isPointOutsideRect(x, y, dragPanelBounds))) {
          hasExitedAddPanel = true
          setAddContentDragActive(true)
        }

        setDropHighlight(getAreaElementFromPoint(x, y))
      },
      end: () => {
        const target = blockButton.value
        const dropArea = highlightedDropArea.value
        const areaHandle = getAreaHandleFromElement(dropArea)
        const didFindValidDropZone = areaHandle.length > 0

        removeDragPreview()
        setDropHighlight(null)
        if (didFindValidDropZone) {
          console.log(props)
          floatingPanels.close(addPanelId)
        } else {
          setAddContentDragActive(false)
        }
        dragPanelBounds = null
        hasExitedAddPanel = false
        target?.classList.remove('opacity-60')
      },
    },
  })
})

onBeforeUnmount(() => {
  removeDragPreview()
  setDropHighlight(null)
  setAddContentDragActive(false)
  dragPanelBounds = null
  hasExitedAddPanel = false
  interactable?.unset?.()
  interactable = null
})
</script>

<template>
  <button
    ref="blockButton"
    type="button"
    @dragstart.prevent
    class="rounded-xl border border-slate-200 bg-white transition hover:border-sky-300 hover:bg-sky-50/80 active:cursor-grabbing cursor-grab"
    :class="props.expanded
      ? 'w-full px-3 py-3 text-left'
      : 'flex aspect-square w-full flex-col items-center justify-center gap-2 px-3 py-3 text-center'"
  >
    <template v-if="props.expanded">
      <div class="flex items-start gap-3">
        <img
          v-if="imageIconSrc"
          :src="imageIconSrc"
          :alt="props.icon?.alt || ''"
          draggable="false"
          class="h-5 w-5 object-contain select-none pointer-events-none"
        >
        <i v-else-if="fontAwesomeClassName" :class="`${fontAwesomeClassName} select-none pointer-events-none`" />
        <span v-else-if="inlineSvg" class="h-5 w-5 text-slate-500 select-none pointer-events-none" v-html="inlineSvg" />
        <span v-else class="h-5 w-5 rounded bg-slate-200" />
        <div class="min-w-0">
          <div class="truncate text-sm font-semibold text-slate-800">{{ props.title }}</div>
          <div class="mt-1 text-xs text-slate-500 line-clamp-2">
            {{ props.description || 'Drag into an editable area to add this block.' }}
          </div>
        </div>
      </div>
    </template>
    <template v-else>
      <img
        v-if="imageIconSrc"
        :src="imageIconSrc"
        :alt="props.icon?.alt || ''"
        draggable="false"
        class="h-5 w-5 object-contain select-none pointer-events-none"
      >
      <i v-else-if="fontAwesomeClassName" :class="`${fontAwesomeClassName} select-none pointer-events-none`" />
      <span v-else-if="inlineSvg" class="h-5 w-5 text-slate-500 select-none pointer-events-none" v-html="inlineSvg" />
      <span v-else class="h-5 w-5 rounded bg-slate-200" />
      <div class="line-clamp-2 text-xs font-semibold leading-tight text-slate-800">{{ props.title }}</div>
    </template>
  </button>
</template>

<style>
.ccm-add-block-drop-target {
  outline: 2px solid rgba(73, 159, 244, 0.9);
  outline-offset: 4px;
}

.ccm-add-block-drag-preview {
  background-color: rgba(240, 249, 255, 0.98);
  transition: none !important;
  animation: none !important;
  will-change: transform;
  backface-visibility: hidden;
}
</style>
