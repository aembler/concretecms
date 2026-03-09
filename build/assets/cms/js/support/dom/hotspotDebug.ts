const cache: {
  enabled: boolean | null
} = {
  enabled: null,
}

function getDebugState(): boolean {
  if (cache.enabled !== null) {
    return cache.enabled
  }

  if (typeof window === 'undefined') {
    cache.enabled = false
    return false
  }

  const windowAny = window as Window & {
    __HOTSPOT_DEBUG__?: boolean | string
  }

  if (windowAny.__HOTSPOT_DEBUG__ === true || windowAny.__HOTSPOT_DEBUG__ === '1') {
    cache.enabled = true
    return true
  }

  const url = new URL(window.location.href)
  const hotSpotDebug = url.searchParams.get('hotspotDebug')
  if (hotSpotDebug === '1' || hotSpotDebug?.toLowerCase() === 'true') {
    cache.enabled = true
    return true
  }

  if (typeof localStorage !== 'undefined' && localStorage.getItem('concrete-hotspot-debug') === '1') {
    cache.enabled = true
    return true
  }

  cache.enabled = false
  return false
}

export function isHotSpotDebugEnabled(): boolean {
  return getDebugState()
}

export function logHotSpotDebug(scope: string, ...args: unknown[]): void {
  if (!isHotSpotDebugEnabled()) {
    return
  }

  console.debug(`[hotspot:${scope}]`, ...args)
}

export function logHotSpotWarn(scope: string, ...args: unknown[]): void {
  if (!isHotSpotDebugEnabled()) {
    return
  }

  console.warn(`[hotspot:${scope}]`, ...args)
}

export function logHotSpotError(scope: string, ...args: unknown[]): void {
  if (!isHotSpotDebugEnabled()) {
    return
  }

  console.error(`[hotspot:${scope}]`, ...args)
}
