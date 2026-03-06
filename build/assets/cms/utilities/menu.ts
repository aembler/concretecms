import { nextTick, onBeforeUnmount, onMounted, ref, unref, watch, type Ref } from 'vue'

type MaybeRef<T> = T | Ref<T>

export function useMenuPositioner(
  rootElement: MaybeRef<HTMLElement | null | undefined>,
  menuElement: MaybeRef<HTMLElement | null | undefined>,
  enabled: MaybeRef<boolean> = true
) {
  const x = ref(0)
  const y = ref(0)
  const toolbarHeight = 72
  const verticalDifferenceAbove = 10
  const verticalDifferenceBelow = 10

  function update() {
    if (!unref(enabled)) {
      return
    }

    const rootEl = unref(rootElement)
    const menuEl = unref(menuElement)
    if (!rootEl || !menuEl) {
      return
    }

    const rect = rootEl.getBoundingClientRect()
    const xOnPage = rect.left
    const yOnPage = rect.top
    const menuHeight = menuEl.offsetHeight
    const elementWidth = rect.width

    const menuLeft = xOnPage + elementWidth / 2
    let menuTop = yOnPage - menuHeight - verticalDifferenceAbove
    if (menuTop < toolbarHeight) {
      menuTop = toolbarHeight + verticalDifferenceBelow
    }

    x.value = menuLeft
    y.value = menuTop
  }

  async function scheduleUpdate() {
    await nextTick()
    update()
  }

  function handleViewportChange() {
    update()
  }

  onMounted(() => {
    window.addEventListener('scroll', handleViewportChange, { passive: true })
    window.addEventListener('resize', handleViewportChange, { passive: true })
    void scheduleUpdate()
  })

  onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleViewportChange)
    window.removeEventListener('resize', handleViewportChange)
  })

  watch([() => unref(rootElement), () => unref(menuElement), () => unref(enabled)], ([isRoot]) => {
    if (!isRoot) {
      return
    }

    void scheduleUpdate()
  }, { flush: 'post' })

  return { x, y, update }
}
