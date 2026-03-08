import type { AddBlockTargetRef, BlockRef } from '../../stores/types/page-operations'

type ScriptDescriptor = {
  src: string | null
  type: string | null
  noModule: boolean
  async: boolean
  defer: boolean
  text: string
}

type ReplaceBlockOptions = {
  originalBlockId: string | number
  replacementHtml: string
  evaluateScripts?: boolean
}

type InsertBlockOptions = {
  target: AddBlockTargetRef
  replacementHtml: string
  evaluateScripts?: boolean
}

export class BlockRenderer {
  async fetchRenderedBlockHtml(block: BlockRef): Promise<string> {
    const base = String((window as any).CCM_DISPATCHER_FILENAME || '')
    const params = new URLSearchParams()
    params.set('arHandle', String(block.arHandle || ''))
    params.set('cID', String(block.cID || ''))
    params.set('bID', String(block.bID || ''))
    params.set('placeholder', '')

    const url = `${base}/ccm/system/block/render?${params.toString()}`
    const response = await fetch(url, {
      method: 'GET',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    })

    if (!response.ok) {
      throw new Error(`Failed to render block: ${response.status} ${response.statusText}`)
    }

    return await response.text()
  }

  async replaceBlock(options: ReplaceBlockOptions): Promise<HTMLElement> {
    const oldElement = this.findExistingBlockElement(options.originalBlockId)
    if (!oldElement) {
      throw new Error(`Original block element was not found: ${String(options.originalBlockId)}`)
    }

    const { newBlockElement, scripts } = this.parseReplacementHtml(options.replacementHtml)
    oldElement.replaceWith(newBlockElement)

    if (options.evaluateScripts !== false) {
      await this.executeScripts(scripts)
    }

    return newBlockElement
  }

  async insertBlock(options: InsertBlockOptions): Promise<HTMLElement> {
    const { newBlockElement, scripts } = this.parseReplacementHtml(options.replacementHtml)
    this.insertBlockElementAtTarget(newBlockElement, options.target)

    if (options.evaluateScripts !== false) {
      await this.executeScripts(scripts)
    }

    return newBlockElement
  }

  findExistingBlockElement(blockId: string | number): HTMLElement | null {
    const rawId = String(blockId || '').replace(/^b/i, '')
    if (!rawId) {
      return null
    }

    const blockIdSelector = `concrete-block[block-id="${this.escapeAttributeSelectorValue(rawId)}"]`
    const blockIdMatched = document.querySelector(blockIdSelector)
    if (blockIdMatched instanceof HTMLElement) {
      return blockIdMatched
    }

    return document.getElementById(`b${rawId}`)
  }

  private parseReplacementHtml(html: string): { newBlockElement: HTMLElement; scripts: ScriptDescriptor[] } {
    const template = document.createElement('template')
    template.innerHTML = html

    const scripts = this.extractScripts(template.content)
    const blockElement = template.content.querySelector('concrete-block')
    if (!(blockElement instanceof HTMLElement)) {
      throw new Error('Replacement HTML did not contain a <concrete-block> element.')
    }

    return {
      newBlockElement: blockElement,
      scripts,
    }
  }

  private insertBlockElementAtTarget(blockElement: HTMLElement, target: AddBlockTargetRef): void {
    const areaHandle = this.escapeAttributeSelectorValue(String(target.areaHandle || ''))
    const pageId = String(target.pageId || '')
    const afterBlockId = String(target.afterBlockId || 0)

    const targetSelector = `concrete-area-block-target[data-page-id="${pageId}"][data-area-handle="${areaHandle}"][data-after-block-id="${afterBlockId}"]`
    const areaTarget = document.querySelector(targetSelector)
    if (areaTarget) {
      areaTarget.insertAdjacentElement('afterend', blockElement)
      return
    }

    // Fallback to direct block-anchor insertion when target markers are missing/stale.
    const afterBlockNumericId = Number(afterBlockId || 0)
    if (afterBlockNumericId > 0) {
      const afterBlockElement = this.findExistingBlockElement(afterBlockNumericId)
      if (afterBlockElement) {
        afterBlockElement.insertAdjacentElement('afterend', blockElement)
        return
      }
    }

    const areaSelector = `concrete-area[page-id="${pageId}"][data-area-handle="${areaHandle}"]`
    const areaElement = document.querySelector(areaSelector)
    if (areaElement) {
      const firstTarget = areaElement.querySelector('concrete-area-block-target')
      if (firstTarget) {
        firstTarget.insertAdjacentElement('afterend', blockElement)
        return
      }
      areaElement.appendChild(blockElement)
      return
    }

    throw new Error(`Could not find insert target for area "${String(target.areaHandle || '')}"`)
  }

  private escapeAttributeSelectorValue(value: string): string {
    return value.replace(/\\/g, '\\\\').replace(/"/g, '\\"')
  }

  private extractScripts(root: ParentNode): ScriptDescriptor[] {
    const scriptNodes = Array.from(root.querySelectorAll('script'))
    const scripts: ScriptDescriptor[] = []

    scriptNodes.forEach((scriptNode) => {
      scripts.push({
        src: scriptNode.getAttribute('src'),
        type: scriptNode.getAttribute('type'),
        noModule: scriptNode.noModule,
        async: scriptNode.async,
        defer: scriptNode.defer,
        text: scriptNode.textContent || '',
      })
      scriptNode.remove()
    })

    return scripts
  }

  private async executeScripts(scripts: ScriptDescriptor[]): Promise<void> {
    for (const script of scripts) {
      await this.executeScript(script)
    }
  }

  private executeScript(script: ScriptDescriptor): Promise<void> {
    return new Promise((resolve, reject) => {
      const node = document.createElement('script')

      if (script.type) {
        node.type = script.type
      }
      node.noModule = script.noModule
      node.async = script.async
      node.defer = script.defer

      if (script.src) {
        node.src = script.src
        node.onload = () => resolve()
        node.onerror = () => reject(new Error(`Failed to load script: ${script.src}`))
      } else {
        node.textContent = script.text
      }

      document.head.appendChild(node)

      if (!script.src) {
        resolve()
      }
    })
  }
}
