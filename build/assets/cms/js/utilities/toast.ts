import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { getConcretePinia } from '../src/Store/pinia'
import type { ToastOperation, ToastVariant } from '../src/Toast/types'

type ToastOptions = {
  title?: string
  message: string
  variant?: ToastVariant
  duration?: number
}

type ToastState = {
  queue: ToastOperation[]
  activeToastId: string | null
  toastContainer: HTMLElement | string | null
}

function createToastOperation(options: ToastOptions): ToastOperation {
  return {
    id: `toast.${Date.now()}.${Math.random().toString(36).slice(2)}`,
    type: 'toast.show',
    status: 'queued',
    title: options.title || 'Notification',
    message: options.message,
    variant: options.variant || 'success',
    duration: options.duration,
  }
}

const useToastStoreBase = defineStore('concrete-ui-toast', {
  state: (): ToastState => ({
    queue: [],
    activeToastId: null,
    toastContainer: null,
  }),

  getters: {
    activeToast(state): ToastOperation | null {
      if (!state.activeToastId) {
        return null
      }

      return state.queue.find((operation) => operation.id === state.activeToastId) ?? null
    },
  },

  actions: {
    show(options: ToastOptions) {
      this.queue.push(createToastOperation(options))
      this.startNextToast()
    },

    startNextToast() {
      if (this.activeToastId) {
        return
      }

      const nextToast = this.queue.find((operation) => operation.status === 'queued')
      if (!nextToast) {
        return
      }

      nextToast.status = 'running'
      this.activeToastId = nextToast.id
    },

    finishToast(id: string, status: 'done' | 'failed' | 'removed') {
      const existingToast = this.queue.find((operation) => operation.id === id)
      if (!existingToast) {
        return
      }

      if (status === 'failed') {
        existingToast.status = 'failed'
      } else {
        this.queue = this.queue.filter((operation) => operation.id !== id)
      }

      if (this.activeToastId === id) {
        this.activeToastId = null
      }

      this.startNextToast()
    },

    completeToast(id: string) {
      this.finishToast(id, 'done')
    },

    failToast(id: string) {
      this.finishToast(id, 'failed')
    },

    removeToast(id: string) {
      this.finishToast(id, 'removed')
    },

    success(title: string, message: string, duration?: number) {
      this.show({ title, message, variant: 'success', duration })
    },

    error(title: string, message: string, duration?: number) {
      this.show({ title, message, variant: 'error', duration })
    },

    info(title: string, message: string, duration?: number) {
      this.show({ title, message, variant: 'info', duration })
    },

    warning(title: string, message: string, duration?: number) {
      this.show({ title, message, variant: 'warning', duration })
    },
  },
})

export function useToast(pinia?: Pinia) {
  const sharedPinia = pinia ?? getConcretePinia()

  return useToastStoreBase(sharedPinia)
}
