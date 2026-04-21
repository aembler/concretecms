import {defineStore} from "pinia";

export const useBlocksStore = defineStore('concrete-ui-blocks', {
  state: () => ({
    blockAreaMap: {} as Record<string, string[]>
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