import { nextTick } from 'vue'
import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { useUiStore } from '@concretecms/backendui'
import { getConcretePinia } from '../src/Store/pinia'
import type { BlockOperation } from '../src/Block/types'
import type { ToastOperation } from '../src/Toast/types'
import type { PendingAddEditorRequest } from '../src/Block/types'
import { refreshHotSpotGeometries } from '../support/dom/hotspot'
import { FOCUSED_EDITING_TARGET_CLASS, FocusedEditingTarget, FOCUSED_EDITING_ROOT_CLASS, FocusedEditingSpotlight} from "../src/HotSpot/FocusedEditingSpotlight";

type DragPointer = { x: number; y: number } | null

const focusedEditingSpotlight = new FocusedEditingSpotlight()

function getFocusedEditingBlockElement(blockId: string | number | null | undefined): HTMLElement | null {
  if (!blockId || typeof document === 'undefined') {
    return null
  }

  return document.querySelector(`concrete-block[block-id="${String(blockId)}"]`)
}

function resolveFocusedEditingElement(target: FocusedEditingTarget): HTMLElement | null {
  if (!target) {
    return null
  }

  if (target.element instanceof HTMLElement) {
    return target.element
  }

  return getFocusedEditingBlockElement(target.blockId)
}


let pageOperationDoneCleanupQueued = false

const useConcreteUiStoreBase = defineStore('concrete-ui', {
  state: () => ({
    toolbar: {
      showTooltips: true,
      showTitles: false,
      useLargeFont: false,
    },
    page: {
      interactionsEnabled: true,
      addContentDragActive: false,
      addContentDragInProgress: false,
      addContentDragPointer: null as DragPointer,
      addContentDraggedItem: null as any,
      addContentDropTarget: null as any,
      pendingAddEditorRequest: null as PendingAddEditorRequest | null,
      operationsQueue: [] as BlockOperation[],
      activeOperationId: null as string | null,
      toastQueue: [] as ToastOperation[],
      activeToastId: null as string | null,
      hoverArea: null as String | null,
      focusedEditingBlockId: null as string | null,
    },
    toastContainer: null as HTMLElement | string | null,
    blockAreaMap: {} as Record<string, string[]>,
    clickProxy: {
      activeElementId: null as string | null,
      hoverElementId: null as string | null,
      doubleClickedElementId: null as string | null,
      activeElementMenuId: null as string | null,
    },
    scroll: {
      y: 0,
      direction: 'down' as 'up' | 'down',
    },
  }),
  actions: {
    setPageInteractionsEnabled(enabled: boolean) {
      if (enabled) {
        refreshHotSpotGeometries()
      }
      this.page.interactionsEnabled = enabled
    },
    setFocusedEditingTarget(target: FocusedEditingTarget) {
      const root = typeof document !== 'undefined' ? document.documentElement : null
      const previousFocusedElement = typeof document !== 'undefined'
        ? document.querySelector<HTMLElement>(`.${FOCUSED_EDITING_TARGET_CLASS}`)
        : null

      previousFocusedElement?.classList.remove(FOCUSED_EDITING_TARGET_CLASS)

      const focusedElement = resolveFocusedEditingElement(target)
      const blockId = target?.blockId ? String(target.blockId) : null
      this.page.focusedEditingBlockId = blockId

      if (!root || !focusedElement) {
        root?.classList.remove(FOCUSED_EDITING_ROOT_CLASS)
        focusedEditingSpotlight.detach()
        this.setPageInteractionsEnabled(true)
        return
      }

      root.classList.add(FOCUSED_EDITING_ROOT_CLASS)
      focusedElement.classList.add(FOCUSED_EDITING_TARGET_CLASS)
      focusedEditingSpotlight.attach(resolveUiMountContainer(), focusedElement)
      void nextTick(() => {
        focusedEditingSpotlight.scheduleUpdate()
      })
      this.setPageInteractionsEnabled(false)
    },
    clearFocusedEditingTarget() {
      this.setFocusedEditingTarget(null)
    },
    setDoubleClickedElementId(id: string) {
      this.clickProxy.doubleClickedElementId = id
      queueMicrotask(() => {
        if (this.clickProxy.doubleClickedElementId === id) {
          this.clickProxy.doubleClickedElementId = null
        }
      })
    },
    clearDoubleClickedElementId() {
      this.clickProxy.doubleClickedElementId = null
    },
    refreshPageAreas() {
      this.clickProxy.activeElementId = null
      this.clickProxy.hoverElementId = null
      refreshHotSpotGeometries()
    },
    updateScroll(y: number) {
      const direction = y < this.scroll.y ? 'up' : 'down'
      this.scroll.direction = direction
      this.scroll.y = y
    },
    setBlockAreaMap(blockId: string, areaPath: string[]) {
      this.blockAreaMap[blockId] = areaPath
    },
    clearBlockAreaMap(blockId: string) {
      delete this.blockAreaMap[blockId]
    },
    setPendingAddEditorRequest(request: PendingAddEditorRequest | null) {
      this.page.pendingAddEditorRequest = request
    },
    clearPendingAddEditorRequest(id?: string) {
      if (!id || this.page.pendingAddEditorRequest?.id === id) {
        this.page.pendingAddEditorRequest = null
      }
    },
    enqueuePageOperation(operation: PageOperation) {
      this.page.operationsQueue.push(operation)
      this.startNextPageOperation()
    },
    enqueueToastOperation(operation: ToastOperation) {
      this.page.toastQueue.push(operation)
      this.startNextToastOperation()
    },
    startNextToastOperation() {
      if (this.page.activeToastId) {
        return
      }

      const nextOperation = this.page.toastQueue.find((operation) => operation.status === 'queued')
      if (!nextOperation) {
        return
      }

      nextOperation.status = 'running'
      this.page.activeToastId = nextOperation.id
    },
    finishToastOperation(id: string, status: 'done' | 'failed' | 'removed') {
      const existingOperation = this.page.toastQueue.find((operation) => operation.id === id)
      if (!existingOperation) {
        return
      }

      if (status === 'failed') {
        existingOperation.status = 'failed'
      } else {
        this.page.toastQueue = this.page.toastQueue.filter((operation) => operation.id !== id)
      }

      if (this.page.activeToastId === id) {
        this.page.activeToastId = null
      }
      this.startNextToastOperation()
    },
    completeToastOperation(id: string) {
      this.finishToastOperation(id, 'done')
    },
    failToastOperation(id: string) {
      this.finishToastOperation(id, 'failed')
    },
    removeToastOperation(id: string) {
      this.finishToastOperation(id, 'removed')
    },
    startNextPageOperation() {
      if (this.page.activeOperationId) {
        return
      }

      if (this.page.operationsQueue.some((operation) => operation.status === 'done')) {
        if (!pageOperationDoneCleanupQueued) {
          pageOperationDoneCleanupQueued = true
          void nextTick(() => {
            pageOperationDoneCleanupQueued = false
            this.page.operationsQueue = this.page.operationsQueue.filter((operation) => operation.status !== 'done')
            if (!this.page.activeOperationId) {
              this.startNextPageOperation()
            }
          })
        }
        return
      }

      const nextOperation = this.page.operationsQueue.find((operation) => operation.status === 'queued')
      if (!nextOperation) {
        return
      }

      nextOperation.status = 'running'
      this.page.activeOperationId = nextOperation.id
    },
    finishPageOperation(id: string, status: 'done' | 'failed' | 'removed') {
      const existingOperation = this.page.operationsQueue.find((operation) => operation.id === id)
      if (!existingOperation) {
        return
      }

      if (status === 'failed') {
        existingOperation.status = 'failed'
      } else if (status === 'done') {
        existingOperation.status = 'done'
      } else {
        this.page.operationsQueue = this.page.operationsQueue.filter((operation) => operation.id !== id)
      }

      if (this.page.activeOperationId === id) {
        this.page.activeOperationId = null
      }
      this.startNextPageOperation()
    },
    completePageOperation(id: string) {
      this.finishPageOperation(id, 'done')
    },
    failPageOperation(id: string) {
      this.finishPageOperation(id, 'failed')
    },
    removePageOperation(id: string) {
      this.finishPageOperation(id, 'removed')
    },
  },
})

function resolveUiMountContainer(): HTMLElement {
  if (typeof document === 'undefined') {
    throw new Error('Cannot resolve UI mount container without a document')
  }

  const uiStore = useUiStore()
  const menuContainer = uiStore.menuContainer

  if (menuContainer instanceof HTMLElement) {
    return menuContainer
  }

  if (typeof menuContainer === 'string' && menuContainer.trim().length > 0) {
    return document.querySelector<HTMLElement>(menuContainer) ?? document.body
  }

  return document.body
}

export function useConcreteUiStore(pinia?: Pinia) {
  const sharedPinia = pinia ?? getConcretePinia()

  return useConcreteUiStoreBase(sharedPinia)
}
