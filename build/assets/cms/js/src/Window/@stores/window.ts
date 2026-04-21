import {defineStore} from "pinia";

export const useWindowStore = defineStore('concrete-ui-window', {
  state: () => ({
    scroll: {
      y: 0,
      direction: 'down' as 'up' | 'down',
    },
  }),
  actions: {
    update(y: number) {
      this.scroll.direction = y < this.scroll.y ? 'up' : 'down'
      this.scroll.y = y
    },
  }
})