import {defineStore} from "pinia";
import type {BlockOperation} from "../types";
import {type QueueState} from '../../Queue/queue'
type BlockQueueState = QueueState<BlockOperation>

export const useBlocksStore = defineStore('concrete-ui-blocks', {
  state: () => ({
    blockAreaMap: {} as Record<string, string[]>,
    operations: {
      queue: [],
      currentId: null,
    } as BlockQueueState,
  }),
  actions: {
    setBlockAreaMap(blockId: string, areaPath: string[]) {
      this.blockAreaMap[blockId] = areaPath
    },
    clearBlockAreaMap(blockId: string) {
      delete this.blockAreaMap[blockId]
    },
  }
})