import { normalizeJsonResponse } from '@concretecms/backendui'

export { normalizeJsonResponse }

type BlockRenderTarget = {
  areaId?: number | string
  areaHandle?: string
  afterBlockId?: number | string
  targetIndex?: number | string
}

export function buildRenderUrl(response: any): string | null {
  const parsedAreaId = parseInt(response?.aID, 10)
  const parsedBlockId = parseInt(response?.bID, 10)
  if (Number.isNaN(parsedAreaId) || Number.isNaN(parsedBlockId)) {
    return null
  }

  const editor = (window as any).Concrete?.getEditMode?.()
  const area = editor?.getAreaByID?.(parsedAreaId)
  const arHandle = response?.arHandle || area?.getHandle?.()
  if (!arHandle) {
    return null
  }

  const cID = response?.cID || (window as any).CCM_CID || 0
  const arEnableGridContainer = area?.getEnableGridContainer?.() ? 1 : 0
  const params = new URLSearchParams()
  params.set('arHandle', String(arHandle))
  params.set('cID', String(cID))
  params.set('bID', String(parsedBlockId))
  params.set('arEnableGridContainer', String(arEnableGridContainer))
  params.set('placeholder', '')
  if (response?.tempFilename) {
    params.set('tempFilename', String(response.tempFilename))
  }

  return `${CCM_DISPATCHER_FILENAME}/ccm/system/block/render?${params.toString()}`
}

function toSafeNumber(value: unknown, fallback = 0): number {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : fallback
}

function escapeSelectorValue(value: string): string {
  const css = (window as any).CSS
  if (css?.escape) {
    return css.escape(value)
  }
  return value.replace(/"/g, '\\"')
}

function executeScripts(scripts: HTMLScriptElement[]): void {
  scripts.forEach((script) => {
    const next = document.createElement('script')
    Array.from(script.attributes).forEach((attribute) => {
      next.setAttribute(attribute.name, attribute.value)
    })
    next.textContent = script.textContent
    document.body.appendChild(next)
    next.remove()
  })
}

function findAreaBlockTarget(target: BlockRenderTarget): HTMLElement | null {
  const areaHandle = String(target.areaHandle || '')
  const areaId = toSafeNumber(target.areaId, 0)
  const afterBlockId = toSafeNumber(target.afterBlockId, 0)

  const selector = `concrete-area-block-target[area-handle="${escapeSelectorValue(areaHandle)}"][area-id="${areaId}"][after-block-id="${afterBlockId}"]`
  return document.querySelector(selector)
}

export function renderBlockHtmlAtDropTarget(
  html: string,
  target: BlockRenderTarget,
): boolean {
  if (!html) {
    return false
  }

  const parser = document.createElement('div')
  parser.innerHTML = html
  const scripts = Array.from(parser.querySelectorAll('script'))
  scripts.forEach((script) => script.remove())
  const nodes = Array.from(parser.childNodes)
  if (nodes.length === 0) {
    return false
  }

  const targetEl = findAreaBlockTarget(target)
  if (targetEl) {
    targetEl.after(...nodes)
    executeScripts(scripts)
    return true
  }

  return false
}
