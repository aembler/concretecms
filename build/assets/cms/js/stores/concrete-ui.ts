import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { getConcretePinia } from './pinia'
import type { PageOperation } from './types/page-operations'
import type { ToastOperation } from './types/page-operations'

type DragPointer = { x: number; y: number } | null
type OperationsDebugWindow = Window & { __CONCRETE_PAGE_OPS_DEBUG__?: boolean }

function getDefaultOperationsDebug(): boolean {
  if (typeof window === 'undefined') {
    return false
  }

  return Boolean((window as OperationsDebugWindow).__CONCRETE_PAGE_OPS_DEBUG__)
}

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
      operationsQueue: [] as PageOperation[],
      activeOperationId: null as string | null,
      toastQueue: [] as ToastOperation[],
      activeToastId: null as string | null,
      operationsDebug: getDefaultOperationsDebug(),
      hoverArea: null as String | null
    },
    toastContainer: null as HTMLElement | string | null,
    blockAreaMap: {} as Record<string, string[]>,
    clickProxy: {
      activeElementId: '',
      hoverElementId: '',
      doubleClickedElementId: '',
      activeElementMenuId: '',
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
      this.page.interactionsEnabled = enabled
    },
    setDoubleClickedElementId(id: string) {
      this.clickProxy.doubleClickedElementId = id
      queueMicrotask(() => {
        if (this.clickProxy.doubleClickedElementId === id) {
          this.clickProxy.doubleClickedElementId = ''
        }
      })
    },
    clearDoubleClickedElementId() {
      this.clickProxy.doubleClickedElementId = ''
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
