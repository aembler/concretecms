import {defineStore} from "pinia";
import { refreshHotSpotGeometries } from "../../HotSpot/hotspot";
import { FOCUSED_EDITING_TARGET_CLASS, FocusedEditingTarget, FOCUSED_EDITING_ROOT_CLASS, FocusedEditingSpotlight} from "../../HotSpot/FocusedEditingSpotlight";
import {nextTick} from "vue";
import {useUiStore} from "@concretecms/backendui";
import type {PendingAddEditorRequest} from "../../Block/types";
type DragPointer = { x: number; y: number } | null

const focusedEditingSpotlight = new FocusedEditingSpotlight()
export const usePageStore = defineStore('concrete-ui-page', {
  state: () => ({
    interactionsEnabled: true,
    add: {
      dragActive: false,
      dragInProgress: false,
      dragPointer: null as DragPointer,
      draggedItem: null as any,
      dropTarget: null as any,
      pendingEditorRequest: null as PendingAddEditorRequest | null,
    },
    clickProxy: {
      activeElementId: null as string | null,
      hoverElementId: null as string | null,
      doubleClickedElementId: null as string | null,
      activeElementMenuId: null as string | null,
    },
  }),
  actions: {
    setPendingAddEditorRequest(request: PendingAddEditorRequest | null) {
      this.add.pendingEditorRequest = request
    },
    clearPendingAddEditorRequest(id?: string) {
      if (!id || this.add.pendingEditorRequest?.id === id) {
        this.add.pendingEditorRequest = null
      }
    },
    setFocusedEditingTarget(target: FocusedEditingTarget) {
      const root = typeof document !== 'undefined' ? document.documentElement : null
      const previousFocusedElement = typeof document !== 'undefined'
          ? document.querySelector<HTMLElement>(`.${FOCUSED_EDITING_TARGET_CLASS}`)
          : null

      previousFocusedElement?.classList.remove(FOCUSED_EDITING_TARGET_CLASS)

      const focusedElement = focusedEditingSpotlight.resolveFocusedEditingElement(target)

      if (!root || !focusedElement) {
        root?.classList.remove(FOCUSED_EDITING_ROOT_CLASS)
        focusedEditingSpotlight.detach()
        this.setPageInteractionsEnabled(true)
        return
      }

      const uiStore = useUiStore()
      let menuContainer = uiStore.menuContainer
      if (typeof(menuContainer) === 'string') {
        menuContainer = document.querySelector<HTMLElement>(menuContainer)
      }

      root.classList.add(FOCUSED_EDITING_ROOT_CLASS)
      focusedElement.classList.add(FOCUSED_EDITING_TARGET_CLASS)
      focusedEditingSpotlight.attach(menuContainer, focusedElement)
      void nextTick(() => {
        focusedEditingSpotlight.scheduleUpdate()
      })
      this.setPageInteractionsEnabled(false)
    },
    clearFocusedEditingTarget() {
      this.setFocusedEditingTarget(null)
    },
    setPageInteractionsEnabled(enabled: boolean) {
      if (enabled) {
        refreshHotSpotGeometries()
      }
      this.interactionsEnabled = enabled
    },
    setDoubleClickedElementId(id: string) {
      this.clickProxy.doubleClickedElementId = id
      queueMicrotask(() => {
        if (this.clickProxy.doubleClickedElementId === id) {
          this.clickProxy.doubleClickedElementId = null
        }
      })
    },
    refreshPageAreas() {
      this.clickProxy.activeElementId = null
      this.clickProxy.hoverElementId = null
      refreshHotSpotGeometries()
    },
  }
})