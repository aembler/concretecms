import { defineStore } from 'pinia'
import type { BlockOperation } from '../types'
import { current, enqueue, finish, type QueueState } from '../../Queue/queue'

type BlockQueueState = QueueState<BlockOperation>

export const useBlocksStore = defineStore('concrete-ui-blocks', {
  state: () => ({
    blockAreaMap: {} as Record<string, string[]>,
    operations: {
      queue: [],
      currentId: null,
    } as BlockQueueState,
  }),
  getters: {
    currentOperation(state): BlockOperation | null {
      return current(state.operations)
    },
  },
  actions: {
    setBlockAreaMap(blockId: string, areaPath: string[]) {
      this.blockAreaMap[blockId] = areaPath
    },
    clearBlockAreaMap(blockId: string) {
      delete this.blockAreaMap[blockId]
    },
    enqueueOperation(operation: BlockOperation) {
      return enqueue(this.operations, operation)
    },
    findOperation(id: string | null | undefined): BlockOperation | null {
      if (!id) {
        return null
      }

      return this.operations.queue.find((operation) => operation.id === id) ?? null
    },
    completeOperation(id: string) {
      return finish(this.operations, id, 'done', { removeOnDone: true })
    },
    failOperation(id: string) {
      return finish(this.operations, id, 'failed')
    },
  }
})
