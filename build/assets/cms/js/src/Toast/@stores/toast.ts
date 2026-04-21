import { defineStore } from 'pinia'
import type { Pinia } from 'pinia'
import { getConcretePinia } from '../../Store/pinia'
import {
  current as currentQueueItem,
  enqueue as enqueueQueueItem,
  finish as finishQueueItem,
  type QueueState,
} from '../../Queue/queue'

type ToastOptions = {
  title?: string
  message: string
  variant?: ToastVariant
  duration?: number
}

import type { Operation } from '../../Queue/queue'

type ToastVariant = 'success' | 'error' | 'info' | 'warning'
interface ToastOperation extends Operation {
  type: 'toast.show'
  title: string
  message: string
  variant?: ToastVariant
  duration?: number
}

type ToastState = QueueState<ToastOperation>

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
    currentId: null,
  }),

  getters: {
    activeToast(state): ToastOperation | null {
      return currentQueueItem(state)
    },
  },

  actions: {
    show(options: ToastOptions) {
      const operation = createToastOperation(options)
      enqueueQueueItem(this, operation)
      return operation
    },

    completeToast(id: string) {
      return finishQueueItem(this, id, 'done', { removeOnDone: true })
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

export function useToastStore(pinia?: Pinia) {
  const sharedPinia = pinia ?? getConcretePinia()

  return useToastStoreBase(sharedPinia)
}
