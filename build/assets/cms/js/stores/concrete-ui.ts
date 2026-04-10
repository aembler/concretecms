import { nextTick } from 'vue'
import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { getConcretePinia } from './pinia'
import type { PageOperation } from './types/page-operations'
import type { ToastOperation } from './types/page-operations'
import type { PendingAddEditorRequest } from './types/page-operations'
import { refreshHotSpotGeometries } from '../support/dom/hotspot'

type DragPointer = { x: number; y: number } | null
type OperationsDebugWindow = Window & { __CONCRETE_PAGE_OPS_DEBUG__?: boolean }
type FocusedEditingTarget = {
  blockId?: string | number | null
  element?: HTMLElement | null
} | null

const FOCUSED_EDITING_ROOT_CLASS = 'concrete-edit-mode-focus'
const FOCUSED_EDITING_TARGET_CLASS = 'concrete-edit-mode-focus-focused'
const FOCUSED_EDITING_SPOTLIGHT_PADDING = 12
const FOCUSED_EDITING_SPOTLIGHT_FILL = 'rgba(0, 0, 0, 0.4)';
const FOCUSED_EDITING_SPOTLIGHT_Z_INDEX = '320'

class FocusedEditingSpotlight {
  private overlayElement: HTMLDivElement | null = null
  private svgElement: SVGSVGElement | null = null
  private pathElement: SVGPathElement | null = null
  private targetElement: HTMLElement | null = null
  private resizeObserver: ResizeObserver | null = null
  private frameId: number | null = null

  attach(target: HTMLElement) {
    if (typeof document === 'undefined' || typeof window === 'undefined') {
      return
    }

    this.targetElement = target
    this.ensureOverlay()
    this.connectResizeObserver()
    window.addEventListener('resize', this.handleViewportChange, { passive: true })
    window.addEventListener('scroll', this.handleViewportChange, { passive: true })
    this.startTracking()
  }

  detach() {
    if (typeof window !== 'undefined') {
      window.removeEventListener('resize', this.handleViewportChange)
      window.removeEventListener('scroll', this.handleViewportChange)
    }

    if (this.frameId !== null && typeof window !== 'undefined') {
      window.cancelAnimationFrame(this.frameId)
      this.frameId = null
    }

    this.disconnectResizeObserver()
    this.targetElement = null
    this.pathElement = null
    this.svgElement = null
    this.overlayElement?.remove()
    this.overlayElement = null
  }

  scheduleUpdate = () => {
    this.update()
  }

  private startTracking() {
    if (typeof window === 'undefined') {
      return
    }

    const tick = () => {
      this.update()
      this.frameId = window.requestAnimationFrame(tick)
    }

    if (this.frameId === null) {
      tick()
    }
  }

  private readonly handleViewportChange = () => {
    this.scheduleUpdate()
  }

  private ensureOverlay() {
    if (this.overlayElement && this.pathElement && this.svgElement) {
      return
    }

    const overlayElement = document.createElement('div')
    overlayElement.className = 'concrete-edit-mode-focus-spotlight'
    overlayElement.setAttribute('aria-hidden', 'true')
    overlayElement.style.position = 'fixed'
    overlayElement.style.inset = '0'
    overlayElement.style.pointerEvents = 'none'
    overlayElement.style.zIndex = FOCUSED_EDITING_SPOTLIGHT_Z_INDEX

    const svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
    svgElement.setAttribute('width', '100%')
    svgElement.setAttribute('height', '100%')
    svgElement.setAttribute('preserveAspectRatio', 'none')
    svgElement.style.display = 'block'

    const pathElement = document.createElementNS('http://www.w3.org/2000/svg', 'path')
    pathElement.setAttribute('fill', FOCUSED_EDITING_SPOTLIGHT_FILL)
    pathElement.setAttribute('fill-rule', 'evenodd')

    svgElement.appendChild(pathElement)
    overlayElement.appendChild(svgElement)
    document.body.appendChild(overlayElement)

    this.overlayElement = overlayElement
    this.svgElement = svgElement
    this.pathElement = pathElement
  }

  private connectResizeObserver() {
    if (!this.targetElement || !window.ResizeObserver) {
      return
    }

    this.disconnectResizeObserver()
    this.resizeObserver = new ResizeObserver(() => {
      this.scheduleUpdate()
    })
    this.resizeObserver.observe(this.targetElement)
  }

  private disconnectResizeObserver() {
    this.resizeObserver?.disconnect()
    this.resizeObserver = null
  }

  private update() {
    if (!this.targetElement || !this.pathElement || !this.svgElement) {
      return
    }

    if (!this.targetElement.isConnected) {
      this.detach()
      return
    }

    const viewportWidth = window.innerWidth
    const viewportHeight = window.innerHeight
    const rect = this.targetElement.getBoundingClientRect()
    const left = clampSpotlightCoordinate(rect.left - FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportWidth)
    const top = clampSpotlightCoordinate(rect.top - FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportHeight)
    const right = clampSpotlightCoordinate(rect.right + FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportWidth)
    const bottom = clampSpotlightCoordinate(rect.bottom + FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportHeight)

    this.svgElement.setAttribute('viewBox', `0 0 ${viewportWidth} ${viewportHeight}`)
    this.pathElement.setAttribute('d', createSpotlightPath({
      viewportWidth,
      viewportHeight,
      left,
      top,
      right,
      bottom,
    }))
  }
}

function clampSpotlightCoordinate(value: number, limit: number): number {
  return Math.max(0, Math.min(limit, Math.round(value)))
}

function createSpotlightPath({
  viewportWidth,
  viewportHeight,
  left,
  top,
  right,
  bottom,
}: {
  viewportWidth: number
  viewportHeight: number
  left: number
  top: number
  right: number
  bottom: number
}) {
  return [
    `M0 0H${viewportWidth}V${viewportHeight}H0Z`,
    `M${left} ${top}H${right}V${bottom}H${left}Z`,
  ].join(' ')
}

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

function getDefaultOperationsDebug(): boolean {
  if (typeof window === 'undefined') {
    return false
  }

  return Boolean((window as OperationsDebugWindow).__CONCRETE_PAGE_OPS_DEBUG__)
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
      operationsQueue: [] as PageOperation[],
      activeOperationId: null as string | null,
      toastQueue: [] as ToastOperation[],
      activeToastId: null as string | null,
      operationsDebug: getDefaultOperationsDebug(),
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
    logPageOperation(event: string, payload: unknown = null) {
      if (!this.page.operationsDebug) {
        return
      }

      console.debug('[ConcreteUiStore:PageOperations]', event, payload)
    },
    setPageOperationsDebug(enabled: boolean) {
      this.page.operationsDebug = enabled
      this.logPageOperation('debug.toggled', { enabled })
    },
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
      focusedEditingSpotlight.attach(focusedElement)
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
      this.logPageOperation('enqueue', operation)
      this.startNextPageOperation()
    },
    enqueueToastOperation(operation: ToastOperation) {
      this.page.toastQueue.push(operation)
      this.logPageOperation('toast.enqueue', operation)
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
      this.logPageOperation('toast.start', nextOperation)
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

      this.logPageOperation(`toast.finish.${status}`, existingOperation)
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
      this.logPageOperation('start', nextOperation)
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

      this.logPageOperation(`finish.${status}`, existingOperation)
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

export function useConcreteUiStore(pinia?: Pinia) {
  const sharedPinia = pinia ?? getConcretePinia()

  return useConcreteUiStoreBase(sharedPinia)
}
