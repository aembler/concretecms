/**
 * Legacy compatibility utility for classic blocks.
 *
 * Modern block and interface code should not rely on runtime CSS/JS injection.
 * Prefer static imports and build-time bundling for new development.
 */
export class ConcreteAssetLoader {
  private static warnedMethods = new Set<string>()

  static getAssetURL(assetPath: string): string {
    this.logUnsupported('getAssetURL', assetPath)
    return assetPath
  }

  static loadJavaScript(assetPath: string): void {
    this.logUnsupported('loadJavaScript', assetPath)
  }

  static loadCSS(assetPath: string): void {
    this.logUnsupported('loadCSS', assetPath)
  }

  static loadOther(contentOrSelector: string): void {
    this.logUnsupported('loadOther', contentOrSelector)
  }

  private static logUnsupported(method: string, payload?: string): void {
    if (this.warnedMethods.has(method)) {
      return
    }
    this.warnedMethods.add(method)

    if (typeof console !== 'undefined' && typeof console.warn === 'function') {
      const suffix = payload ? ` Requested: ${payload}` : ''
      console.warn(
        `[ConcreteAssetLoader.${method}] is no longer supported in this runtime.${suffix} Please migrate to an explicit asset-loading strategy.`,
      )
    }
  }
}

declare global {
  interface Window {
    ConcreteAssetLoader: typeof ConcreteAssetLoader
  }
}
