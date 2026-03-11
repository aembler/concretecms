import { nextTick, onBeforeUnmount, onMounted, ref, unref, watch, type InjectionKey, type Ref } from 'vue'

type MaybeRef<T> = T | Ref<T>
type MaybeGetter<T> = (() => T) | T

export type HotSpotGeometry = {
  top: number
  left: number
  pageTop: number
  pageLeft: number
  width: number
  height: number
  right: number
  bottom: number
}
type HotSpotGeometryRefs = {
  top: Ref<number>
  left: Ref<number>
  pageTop: Ref<number>
  pageLeft: Ref<number>
  width: Ref<number>
  height: Ref<number>
  right: Ref<number>
  bottom: Ref<number>
  isScrollSettled: Ref<boolean>
  updateGeometry: () => void
}

export type HotSpotBadgeGeometry = {
  top: Ref<number>
  left: Ref<number>
  bottom: Ref<number>
  width: Ref<number>
}

export const HOT_SPOT_BADGE_GEOMETRY_KEY = Symbol('hot-spot-badge-geometry') as InjectionKey<HotSpotBadgeGeometry>
const hotSpotGeometryRefreshers = new Set<() => void>()

export function refreshHotSpotGeometries() {
  hotSpotGeometryRefreshers.forEach((refreshGeometry) => refreshGeometry())
}

export function useHotSpotGeometry(
  rootElement: MaybeRef<HTMLElement | null | undefined> | MaybeGetter<HTMLElement | null | undefined>
) {
  const top = ref(0)
  const left = ref(0)
  const pageTop = ref(0)
  const pageLeft = ref(0)
  const width = ref(0)
  const height = ref(0)
  const right = ref(0)
  const bottom = ref(0)
  const isScrollSettled = ref(true)
  let scrollSettledTimer: ReturnType<typeof setTimeout> | null = null

  let resizeObserver: ResizeObserver | null = null

  function resolveRootElement() {
    if (typeof rootElement === 'function') {
      return rootElement()
    }

    return unref(rootElement)
  }

  function resetGeometry() {
    top.value = 0
    left.value = 0
    pageTop.value = 0
    pageLeft.value = 0
    width.value = 0
    height.value = 0
    right.value = 0
    bottom.value = 0
  }

  function updateGeometry() {
    const root = resolveRootElement()
    if (!root || !root.getBoundingClientRect) {
      resetGeometry()
      return
    }

    const rect = root.getBoundingClientRect()
    top.value = rect.top
    left.value = rect.left
    right.value = rect.right
    bottom.value = rect.bottom
    width.value = rect.width
    height.value = rect.height
    pageTop.value = rect.top + window.scrollY
    pageLeft.value = rect.left + window.scrollX

  }
  const refreshGeometry = () => updateGeometry()

  async function scheduleUpdate() {
    await nextTick()
    updateGeometry()
  }

  function handleViewportChange() {
    updateGeometry()
  }

  function handleScroll() {
    isScrollSettled.value = false

    if (scrollSettledTimer) {
      clearTimeout(scrollSettledTimer)
    }

    scrollSettledTimer = setTimeout(() => {
      isScrollSettled.value = true
    }, 150)

    updateGeometry()
  }

  function handleScrollStart() {
    isScrollSettled.value = false
  }

  function disconnectResizeObserver() {
    if (!resizeObserver) {
      return
    }
    resizeObserver.disconnect()
    resizeObserver = null
  }

  function connectResizeObserver(root: HTMLElement | null | undefined) {
    if (!root || !(root instanceof Element) || !window.ResizeObserver) {
      return
    }

    disconnectResizeObserver()
    resizeObserver = new ResizeObserver(updateGeometry)
    resizeObserver.observe(root)
  }

  onMounted(() => {
    window.addEventListener('wheel', handleScrollStart, { passive: true })
    window.addEventListener('scroll', handleScroll, { passive: true })
    window.addEventListener('touchstart', handleScrollStart, { passive: true })
    window.addEventListener('touchmove', handleScrollStart, { passive: true })
    window.addEventListener('resize', handleViewportChange, { passive: true })
    connectResizeObserver(resolveRootElement())
    hotSpotGeometryRefreshers.add(refreshGeometry)
    void scheduleUpdate()
  })

  onBeforeUnmount(() => {
    window.removeEventListener('wheel', handleScrollStart)
    window.removeEventListener('touchstart', handleScrollStart)
    window.removeEventListener('touchmove', handleScrollStart)
    window.removeEventListener('scroll', handleScroll)
    window.removeEventListener('resize', handleViewportChange)
    hotSpotGeometryRefreshers.delete(refreshGeometry)
    disconnectResizeObserver()
    if (scrollSettledTimer) {
      clearTimeout(scrollSettledTimer)
      scrollSettledTimer = null
    }
  })

  watch(resolveRootElement, (root) => {
    connectResizeObserver(root)
    void scheduleUpdate()
  }, { flush: 'post' })

  return {
    top,
    left,
    pageTop,
    pageLeft,
    width,
    height,
    right,
    bottom,
    isScrollSettled,
    updateGeometry,
  } satisfies HotSpotGeometryRefs
}
