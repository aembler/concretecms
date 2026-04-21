import {defineStore, Pinia} from "pinia";
import {getConcretePinia} from "../../Store/pinia";

const useToolbarBase = defineStore('concrete-ui-toolbar', {
  state: () => ({
    showTooltips: true,
    showTitles: false,
    useLargeFont: false,
  })
})
export function useToolbarStore(pinia?: Pinia) {
  const sharedPinia = pinia ?? getConcretePinia()

  return useToolbarBase(sharedPinia)
}
