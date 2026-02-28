<script setup lang="ts">
import interact from 'interactjs'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { normalizeJsonResponse, useAjax, useFloatingPanelsStore, useUiStore } from '@concretecms/backendui'

type PanelIcon = {
  type: string
  src?: string
  alt?: string
  className?: string
  svg?: string
}

type BlockTypeEditor = {
  component: string
} | null

type AddContentDropTarget = {
  areaId?: number | string
  areaHandle?: string
  afterBlockId?: number | string
  targetIndex?: number | string
}

type AddContentDraggedItem = {
  type?: string
  payload?: {
    blockTypeId?: number
    blockTypeHandle?: string
    title?: string
    description?: string
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
  editor?: BlockTypeEditor
}>(), {
  description: '',
  expanded: false,
  blockTypeId: 0,
  blockTypeHandle: '',
  editor: null,
})

const iconType = computed(() => props.icon?.type ?? '')
const imageIconSrc = computed(() => (iconType.value === 'image-file' ? props.icon?.src : ''))
const fontAwesomeClassName = computed(() => (iconType.value === 'font-awesome' ? props.icon?.className ?? '' : ''))
const inlineSvg = computed(() => (iconType.value === 'inline-svg' ? props.icon?.svg ?? '' : ''))
const uiStore = useUiStore()
const floatingPanels = useFloatingPanelsStore()
const { request } = useAjax()
const pageState = computed(() => (uiStore.page as any))
const addPanelId = 'toolbar:add'
const blockButton = ref<HTMLButtonElement | null>(null)
let interactable: any = null
let dragPreview: HTMLElement | null = null
let dragPreviewContainer: HTMLElement | null = null
let dragPanelBounds: DOMRect | null = null
let hasExitedAddPanel = false
let isSubmittingAddWithoutEditor = false

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
  uiStore.page.addContentDragActive = next
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

function submitAddBlockWithoutEditor(dropTarget: AddContentDropTarget) {
  if (isSubmittingAddWithoutEditor) {
    return
  }

  const arHandle = String(dropTarget?.areaHandle || '')
  const btID = Number(props.blockTypeId || 0)
  if (!arHandle || btID <= 0) {
    return
  }

  isSubmittingAddWithoutEditor = true
  const cID = Number((window as any).CCM_CID || 0)
  const ccmToken = String((window as any).CCM_SECURITY_TOKEN || '')
  const dragAreaBlockID = Number(dropTarget?.afterBlockId || 0)

  const submitParams = new URLSearchParams()
  submitParams.set('cID', String(cID))
  submitParams.set('arHandle', arHandle)
  submitParams.set('btID', String(btID))
  submitParams.set('mode', 'edit')
  submitParams.set('add', '1')
  submitParams.set('ccm_token', ccmToken)
  submitParams.set('dragAreaBlockID', String(dragAreaBlockID))
  // TODO: add legacy arCustomTemplates support when custom templates are wired into uiStore.page state.

  request({
    url: `${CCM_DISPATCHER_FILENAME}/ccm/system/dialogs/page/add_block/submit?${submitParams.toString()}`,
    method: 'GET',
    skipResponseValidation: true,
    onSuccess: (response) => {
      const normalizedResponse: any = normalizeJsonResponse(response)
      if (normalizedResponse?.error || (Array.isArray(normalizedResponse?.errors) && normalizedResponse.errors.length > 0)) {
        return
      }
      const blockInfo = {
        bID: Number(normalizedResponse?.bID || 0),
        arHandle: String(normalizedResponse?.arHandle || dropTarget?.areaHandle || ''),
        cID: Number(normalizedResponse?.cID || (window as any).CCM_CID || 0),
        dropTarget: {
          areaId: Number(dropTarget?.areaId || 0),
          areaHandle: String(dropTarget?.areaHandle || ''),
          afterBlockId: Number(dropTarget?.afterBlockId || 0),
          targetIndex: Number(dropTarget?.targetIndex || 0),
        },
      }
      alert(`[FPO] Add block success\n${JSON.stringify(blockInfo, null, 2)}`)
    },
    onComplete: () => {
      isSubmittingAddWithoutEditor = false
    },
  })
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
        const didFindValidDropZone = Boolean(activeDropTarget?.areaHandle && draggedItem?.type === 'blockType')

        removeDragPreview()
        if (didFindValidDropZone) {
          floatingPanels.close(addPanelId)
          const addEditor = draggedItem?.payload?.editor ?? null
          if (addEditor === null && activeDropTarget) {
            submitAddBlockWithoutEditor({ ...activeDropTarget })
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

<style>
.ccm-add-block-drag-preview {
  background-color: rgba(240, 249, 255, 0.98);
  transition: none !important;
  animation: none !important;
  will-change: transform;
  backface-visibility: hidden;
}
</style>
