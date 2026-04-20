/**
 * Compatibility shim for legacy code that still calls `window.ConcreteAssetLoader`.
 *
 * This runtime no longer performs dynamic CSS/JS asset injection. The methods on
 * this class are retained only to preserve older interfaces and reduce repeated
 * console noise while those callers are migrated.
 *
 * New code should not use this API.
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

  // Warn once per method so legacy callers remain visible without flooding the console.
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
