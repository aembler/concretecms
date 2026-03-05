import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { getConcretePinia } from './pinia'
import type { PageOperation } from './types/page-operations'

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
      operationsDebug: getDefaultOperationsDebug(),
    },
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
    updateScroll(y: number) {
      const direction = y < this.scroll.y ? 'up' : 'down'
      this.scroll.direction = direction
      this.scroll.y = y
    },
    enqueuePageOperation(operation: PageOperation) {
      this.page.operationsQueue.push(operation)
      this.logPageOperation('enqueue', operation)
      this.startNextPageOperation()
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
