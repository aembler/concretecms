import { nextTick } from 'vue'
import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { getConcretePinia } from '../src/Store/pinia'
import type { BlockOperation } from '../src/Block/types'


let pageOperationDoneCleanupQueued = false

const useConcreteUiStoreBase = defineStore('concrete-ui', {
  state: () => ({
    page: {
      operationsQueue: [] as BlockOperation[],
      activeOperationId: null as string | null,
    }
  }),
  actions: {
    enqueuePageOperation(operation: BlockOperation) {
      this.page.operationsQueue.push(operation)
      this.startNextPageOperation()
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

export function useConcreteUiStore(pinia?: Pinia) {
  const sharedPinia = pinia ?? getConcretePinia()

  return useConcreteUiStoreBase(sharedPinia)
}
