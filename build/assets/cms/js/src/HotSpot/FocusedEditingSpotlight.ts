type FocusedEditingTarget = {
    blockId?: string | number | null
    element?: HTMLElement | null
} | null

const FOCUSED_EDITING_ROOT_CLASS = 'concrete-edit-mode-focus'
const FOCUSED_EDITING_TARGET_CLASS = 'concrete-edit-mode-focus-focused'
const FOCUSED_EDITING_SPOTLIGHT_PADDING = 12
const FOCUSED_EDITING_SPOTLIGHT_RADIUS = 6
const FOCUSED_EDITING_SPOTLIGHT_FILL = 'rgba(0, 0, 0, 0.4)';
const FOCUSED_EDITING_SPOTLIGHT_TRANSITION_MS = 500

class FocusedEditingSpotlight {
    private rootElement: HTMLElement | null = null
    private overlayElement: HTMLDivElement | null = null
    private svgElement: SVGSVGElement | null = null
    private pathElement: SVGPathElement | null = null
    private targetElement: HTMLElement | null = null
    private resizeObserver: ResizeObserver | null = null
    private frameId: number | null = null
    private showFrameId: number | null = null
    private teardownTimeoutId: number | null = null

    private clampSpotlightCoordinate(value: number, limit: number): number {
        return Math.max(0, Math.min(limit, Math.round(value)))
    }

    attach(root: HTMLElement, target: HTMLElement) {
        if (typeof document === 'undefined' || typeof window === 'undefined') {
            return
        }

        this.rootElement = root
        this.targetElement = target
        this.ensureOverlay()
        this.connectResizeObserver()
        window.addEventListener('resize', this.handleViewportChange, {passive: true})
        window.addEventListener('scroll', this.handleViewportChange, {passive: true})
        this.startTracking()
        this.showOverlay()
    }

    detach() {
        if (typeof window !== 'undefined') {
            window.removeEventListener('resize', this.handleViewportChange)
            window.removeEventListener('scroll', this.handleViewportChange)
        }

        if (this.frameId !== null && typeof window !== 'undefined') {
            window.cancelAnimationFrame(this.frameId)
            this.frameId = null
        }

        this.disconnectResizeObserver()
        this.targetElement = null
        this.hideOverlay()
    }

    scheduleUpdate = () => {
        this.update()
    }

    private startTracking() {
        if (typeof window === 'undefined') {
            return
        }

        const tick = () => {
            this.update()
            this.frameId = window.requestAnimationFrame(tick)
        }

        if (this.frameId === null) {
            tick()
        }
    }

    private readonly handleViewportChange = () => {
        this.scheduleUpdate()
    }

    private ensureOverlay() {
        if (this.overlayElement && this.pathElement && this.svgElement) {
            return
        }

        const overlayElement = document.createElement('div')
        overlayElement.className = 'concrete-edit-mode-focus-spotlight transition-opacity z-(--index-layer-edit-backdrop) duration-500 ease-out opacity-0'
        overlayElement.setAttribute('aria-hidden', 'true')
        overlayElement.style.position = 'fixed'
        overlayElement.style.inset = '0'
        overlayElement.style.pointerEvents = 'none'

        const svgElement = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
        svgElement.setAttribute('width', '100%')
        svgElement.setAttribute('height', '100%')
        svgElement.setAttribute('preserveAspectRatio', 'none')
        svgElement.style.display = 'block'

        const pathElement = document.createElementNS('http://www.w3.org/2000/svg', 'path')
        pathElement.setAttribute('fill', FOCUSED_EDITING_SPOTLIGHT_FILL)
        pathElement.setAttribute('fill-rule', 'evenodd')

        svgElement.appendChild(pathElement)
        overlayElement.appendChild(svgElement)
        this.rootElement.appendChild(overlayElement)

        this.overlayElement = overlayElement
        this.svgElement = svgElement
        this.pathElement = pathElement
    }

    private showOverlay() {
        if (!this.overlayElement || typeof window === 'undefined') {
            return
        }

        if (this.showFrameId !== null) {
            window.cancelAnimationFrame(this.showFrameId)
            this.showFrameId = null
        }

        if (this.teardownTimeoutId !== null) {
            window.clearTimeout(this.teardownTimeoutId)
            this.teardownTimeoutId = null
        }

        this.overlayElement.classList.remove('opacity-100')
        this.overlayElement.classList.add('opacity-0')

        this.showFrameId = window.requestAnimationFrame(() => {
            this.showFrameId = window.requestAnimationFrame(() => {
                this.showFrameId = null

                if (!this.overlayElement) {
                    return
                }

                this.overlayElement.classList.remove('opacity-0')
                this.overlayElement.classList.add('opacity-100')
            })
        })
    }

    public resolveFocusedEditingElement(target: FocusedEditingTarget): HTMLElement | null {
        if (!target) {
            return null
        }

        if (target.element instanceof HTMLElement) {
            return target.element
        }

        if (!target.blockId) {
            return null
        }

        return document.querySelector(`concrete-block[block-id="${String(target.blockId)}"]`)
    }

    private hideOverlay() {
        if (!this.overlayElement || typeof window === 'undefined') {
            this.destroyOverlay()
            return
        }

        if (this.showFrameId !== null) {
            window.cancelAnimationFrame(this.showFrameId)
            this.showFrameId = null
        }

        if (this.teardownTimeoutId !== null) {
            window.clearTimeout(this.teardownTimeoutId)
        }

        this.overlayElement.classList.remove('opacity-100')
        this.overlayElement.classList.add('opacity-0')
        this.teardownTimeoutId = window.setTimeout(() => {
            this.destroyOverlay()
        }, FOCUSED_EDITING_SPOTLIGHT_TRANSITION_MS)
    }

    private destroyOverlay() {
        if (this.showFrameId !== null && typeof window !== 'undefined') {
            window.cancelAnimationFrame(this.showFrameId)
            this.showFrameId = null
        }

        if (this.teardownTimeoutId !== null && typeof window !== 'undefined') {
            window.clearTimeout(this.teardownTimeoutId)
            this.teardownTimeoutId = null
        }

        this.pathElement = null
        this.svgElement = null
        this.overlayElement?.remove()
        this.overlayElement = null
    }

    private connectResizeObserver() {
        if (!this.targetElement || !window.ResizeObserver) {
            return
        }

        this.disconnectResizeObserver()
        this.resizeObserver = new ResizeObserver(() => {
            this.scheduleUpdate()
        })
        this.resizeObserver.observe(this.targetElement)
    }

    private disconnectResizeObserver() {
        this.resizeObserver?.disconnect()
        this.resizeObserver = null
    }

    private createRoundedRectPath({
                                      left,
                                      top,
                                      right,
                                      bottom,
                                      radius,
                                  }: {
        left: number
        top: number
        right: number
        bottom: number
        radius: number
    }) {
        const width = Math.max(0, right - left)
        const height = Math.max(0, bottom - top)
        const clampedRadius = Math.max(0, Math.min(radius, width / 2, height / 2))

        if (clampedRadius === 0) {
            return `M${left} ${top}H${right}V${bottom}H${left}Z`
        }

        return [
            `M${left + clampedRadius} ${top}`,
            `H${right - clampedRadius}`,
            `A${clampedRadius} ${clampedRadius} 0 0 1 ${right} ${top + clampedRadius}`,
            `V${bottom - clampedRadius}`,
            `A${clampedRadius} ${clampedRadius} 0 0 1 ${right - clampedRadius} ${bottom}`,
            `H${left + clampedRadius}`,
            `A${clampedRadius} ${clampedRadius} 0 0 1 ${left} ${bottom - clampedRadius}`,
            `V${top + clampedRadius}`,
            `A${clampedRadius} ${clampedRadius} 0 0 1 ${left + clampedRadius} ${top}`,
            'Z',
        ].join('')
    }

    private createSpotlightPath({
                                    viewportWidth,
                                    viewportHeight,
                                    left,
                                    top,
                                    right,
                                    bottom,
                                }: {
        viewportWidth: number
        viewportHeight: number
        left: number
        top: number
        right: number
        bottom: number
    }) {
        return [
            `M0 0H${viewportWidth}V${viewportHeight}H0Z`,
            this.createRoundedRectPath({
                left,
                top,
                right,
                bottom,
                radius: FOCUSED_EDITING_SPOTLIGHT_RADIUS,
            }),
        ].join(' ')
    }

    private update() {
        if (!this.targetElement || !this.pathElement || !this.svgElement) {
            return
        }

        if (!this.targetElement.isConnected) {
            this.detach()
            return
        }

        const viewportWidth = window.innerWidth
        const viewportHeight = window.innerHeight
        const rect = this.targetElement.getBoundingClientRect()
        const left = this.clampSpotlightCoordinate(rect.left - FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportWidth)
        const top = this.clampSpotlightCoordinate(rect.top - FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportHeight)
        const right = this.clampSpotlightCoordinate(rect.right + FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportWidth)
        const bottom = this.clampSpotlightCoordinate(rect.bottom + FOCUSED_EDITING_SPOTLIGHT_PADDING, viewportHeight)

        this.svgElement.setAttribute('viewBox', `0 0 ${viewportWidth} ${viewportHeight}`)
        this.pathElement.setAttribute('d', this.createSpotlightPath({
            viewportWidth,
            viewportHeight,
            left,
            top,
            right,
            bottom,
        }))
    }
}

export {
    FOCUSED_EDITING_TARGET_CLASS,
    FOCUSED_EDITING_ROOT_CLASS,
    FocusedEditingTarget,
    FocusedEditingSpotlight
}
