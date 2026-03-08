import { createPinia as createBackendUiPinia } from '@concretecms/backendui'
import type { Pinia } from 'pinia'

type BackendUiPiniaWindow = Window & { __CONCRETE_BACKENDUI_PINIA__?: Pinia }

let concretePinia: Pinia | undefined

export function createConcretePinia(): Pinia {
  if (!concretePinia) {
    concretePinia = createBackendUiPinia()
  }

  return concretePinia
}

export function getConcretePinia(): Pinia | undefined {
  return (
    concretePinia ??
    (typeof window !== 'undefined'
      ? (window as BackendUiPiniaWindow).__CONCRETE_BACKENDUI_PINIA__
      : undefined)
  )
}
