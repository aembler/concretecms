<script setup lang="ts">
import interact from 'interactjs'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useFloatingPanelsStore, useUiStore } from '@concretecms/backendui'
import { useConcreteUiStore } from '../../../../stores/concrete-ui'
import type { AddBlockOperation, AddBlockTargetRef } from '../../../Block/types'
import type { BlockTypeEditor } from '../../../Block/Editor/types'
import { useBlockEditorRegistry } from '../../../Block/Editor/registry'

type PanelIcon = {
  type: string
  src?: string
  alt?: string
  className?: string
  svg?: string
}

type AddContentDropTarget = {
  areaId?: number | string
  pageId?: number | string
  areaHandle?: string
  afterBlockId?: number | string
  targetIndex?: number | string
  container?: {
    start?: string
    end?: string
  } | null
}

type AddContentDraggedItem = {
  type?: string
  payload?: {
    blockTypeId?: number
    blockTypeHandle?: string
    title?: string
    description?: string
    ignoreContainer?: boolean
    editor?: BlockTypeEditor
  }
}

const props = withDefaults(defineProps<{
  icon?: PanelIcon
  title: string
  description?: string
  expanded?: boolean
  blockTypeId?: number
  blockTypeHandle?: string
  ignoreContainer?: boolean
  editor?: BlockTypeEditor
}>(), {
  description: '',
  expanded: false,
  blockTypeId: 0,
  blockTypeHandle: '',
  ignoreContainer: false,
  editor: null,
})

const iconType = computed(() => props.icon?.type ?? '')
const imageIconSrc = computed(() => (iconType.value === 'image-file' ? props.icon?.src : ''))
const fontAwesomeClassName = computed(() => (iconType.value === 'font-awesome' ? props.icon?.className ?? '' : ''))
const inlineSvg = computed(() => (iconType.value === 'inline-svg' ? props.icon?.svg ?? '' : ''))
const uiStore = useUiStore()
const concreteUiStore = useConcreteUiStore()
const blockEditorRegistry = useBlockEditorRegistry()
const floatingPanels = useFloatingPanelsStore()
const pageState = computed(() => (concreteUiStore.page as any))
const addPanelId = 'toolbar:add'
const blockButton = ref<HTMLButtonElement | null>(null)
let interactable: any = null
let dragPreview: HTMLElement | null = null
let dragPreviewContainer: HTMLElement | null = null
let dragPanelBounds: DOMRect | null = null
let hasExitedAddPanel = false

function ensureAddContentPageState() {
  if (typeof pageState.value.addContentDragInProgress === 'undefined') {
    pageState.value.addContentDragInProgress = false
  }
  if (typeof pageState.value.addContentDragPointer === 'undefined') {
    pageState.value.addContentDragPointer = null
  }
  if (typeof pageState.value.addContentDraggedItem === 'undefined') {
    pageState.value.addContentDraggedItem = null
  }
  if (typeof pageState.value.addContentDropTarget === 'undefined') {
    pageState.value.addContentDropTarget = null
  }
}

function getClientCoordinates(event: any): { x: number; y: number } {
  return {
    x: Number(event?.clientX ?? event?.client?.x ?? 0),
    y: Number(event?.clientY ?? event?.client?.y ?? 0),
  }
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
  concreteUiStore.page.addContentDragActive = next
}

function setAddContentDragInProgress(next: boolean) {
  pageState.value.addContentDragInProgress = next
}

function setAddContentDragPointer(x: number, y: number) {
  pageState.value.addContentDragPointer = { x, y }
}

function clearAddContentDragPointer() {
  pageState.value.addContentDragPointer = null
}

function setAddContentDraggedItem() {
  pageState.value.addContentDraggedItem = {
    type: 'blockType',
    payload: {
      blockTypeId: props.blockTypeId ?? 0,
      blockTypeHandle: props.blockTypeHandle ?? '',
      title: props.title,
      description: props.description ?? '',
      ignoreContainer: props.ignoreContainer ?? false,
      editor: props.editor ?? null,
    },
  }
}

function clearAddContentDraggedItem() {
  pageState.value.addContentDraggedItem = null
}

function clearAddContentDropTarget() {
  pageState.value.addContentDropTarget = null
}

function isPointOutsideRect(x: number, y: number, rect: DOMRect): boolean {
  return x < rect.left || x > rect.right || y < rect.top || y > rect.bottom
}

function enqueueAddBlockOperation(dropTarget: AddContentDropTarget, draggedItem: AddContentDraggedItem) {
  const areaHandle = String(dropTarget.areaHandle || '')
  const pageId = Number(dropTarget.pageId || 0)
  const blockTypeId = Number(draggedItem?.payload?.blockTypeId || props.blockTypeId || 0)
  if (!areaHandle || pageId <= 0 || blockTypeId <= 0) {
    return
  }

  const operation: AddBlockOperation = {
    id: `block.add.${blockTypeId}.${Date.now()}`,
    type: 'block.add',
    status: 'queued',
    blockTypeId,
    blockTypeHandle: String(draggedItem?.payload?.blockTypeHandle || props.blockTypeHandle || ''),
    blockTitle: String(draggedItem?.payload?.title || props.title || ''),
    ignoreContainer: Boolean(draggedItem?.payload?.ignoreContainer ?? props.ignoreContainer ?? false),
    target: {
      areaId: Number(dropTarget.areaId || 0),
      areaHandle,
      pageId,
      afterBlockId: Number(dropTarget.afterBlockId || 0),
      targetIndex: Number(dropTarget.targetIndex || 0),
      container: dropTarget.container ?? null,
    },
  }

  concreteUiStore.enqueuePageOperation(operation)
}

function toAddBlockTarget(dropTarget: AddContentDropTarget): AddBlockTargetRef {
  return {
    areaId: Number(dropTarget.areaId || 0),
    areaHandle: String(dropTarget.areaHandle || ''),
    pageId: Number(dropTarget.pageId || 0),
    afterBlockId: Number(dropTarget.afterBlockId || 0),
    targetIndex: Number(dropTarget.targetIndex || 0),
    container: dropTarget.container ?? null,
  }
}

function isValidBlockTypeEditor(editor: BlockTypeEditor): editor is NonNullable<BlockTypeEditor> {
  return Boolean(
    editor
    && typeof editor.component === 'string'
    && blockEditorRegistry.hasEditorComponent(editor.component)
  )
}

function requestAddEditor(dropTarget: AddContentDropTarget, draggedItem: AddContentDraggedItem) {
  const editor = draggedItem?.payload?.editor
  if (!isValidBlockTypeEditor(editor)) {
    return
  }

  concreteUiStore.setPendingAddEditorRequest({
    id: `add-editor.${String(draggedItem?.payload?.blockTypeId || props.blockTypeId || 0)}.${Date.now()}`,
    blockTypeId: Number(draggedItem?.payload?.blockTypeId || props.blockTypeId || 0),
    blockTypeHandle: String(draggedItem?.payload?.blockTypeHandle || props.blockTypeHandle || ''),
    blockTitle: String(draggedItem?.payload?.title || props.title || ''),
    ignoreContainer: Boolean(draggedItem?.payload?.ignoreContainer ?? props.ignoreContainer ?? false),
    target: toAddBlockTarget(dropTarget),
    editor,
  })

  setAddContentDragActive(false)
  floatingPanels.close(addPanelId)
}

onMounted(() => {
  ensureAddContentPageState()
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
        setAddContentDragInProgress(true)
        clearAddContentDropTarget()
        setAddContentDraggedItem()
        setAddContentDragPointer(x, y)
        target.classList.add('opacity-60')
        createDragPreview(target, x, y)
      },
      move: (event: any) => {
        const { x, y } = getClientCoordinates(event)
        moveDragPreview(x, y)
        setAddContentDragPointer(x, y)

        if (!hasExitedAddPanel && (!dragPanelBounds || isPointOutsideRect(x, y, dragPanelBounds))) {
          hasExitedAddPanel = true
          setAddContentDragActive(true)
        }
      },
      end: () => {
        const target = blockButton.value
        const activeDropTarget = (pageState.value.addContentDropTarget || null) as AddContentDropTarget | null
        const draggedItem = (pageState.value.addContentDraggedItem || null) as AddContentDraggedItem | null
        const didFindValidDropZone = Boolean(activeDropTarget?.areaHandle && activeDropTarget?.pageId && draggedItem?.type === 'blockType')

        removeDragPreview()
        if (didFindValidDropZone) {
          const addEditor = draggedItem?.payload?.editor ?? null
          if (addEditor === null && activeDropTarget && draggedItem) {
            floatingPanels.close(addPanelId)
            enqueueAddBlockOperation({ ...activeDropTarget }, draggedItem)
          } else if (isValidBlockTypeEditor(addEditor) && activeDropTarget && draggedItem) {
            requestAddEditor(activeDropTarget, draggedItem)
          }
        }
        setAddContentDragActive(false)
        setAddContentDragInProgress(false)
        clearAddContentDragPointer()
        clearAddContentDraggedItem()
        clearAddContentDropTarget()
        dragPanelBounds = null
        hasExitedAddPanel = false
        target?.classList.remove('opacity-60')
      },
    },
  })
})

onBeforeUnmount(() => {
  removeDragPreview()
  setAddContentDragActive(false)
  setAddContentDragInProgress(false)
  clearAddContentDragPointer()
  clearAddContentDraggedItem()
  clearAddContentDropTarget()
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
