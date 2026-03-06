import { useConcreteUiStore } from '../stores/concrete-ui'
import type { ToastOperation, ToastVariant } from '../stores/types/page-operations'

type ToastOptions = {
  title?: string
  message: string
  variant?: ToastVariant
  duration?: number
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

export function useToast() {
  const uiStore = useConcreteUiStore()

  function show(options: ToastOptions) {
    uiStore.enqueueToastOperation(createToastOperation(options))
  }

  function success(title: string, message: string, duration?: number) {
    show({ title, message, variant: 'success', duration })
  }

  function error(title: string, message: string, duration?: number) {
    show({ title, message, variant: 'error', duration })
  }

  function info(title: string, message: string, duration?: number) {
    show({ title, message, variant: 'info', duration })
  }

  function warning(title: string, message: string, duration?: number) {
    show({ title, message, variant: 'warning', duration })
  }

  return {
    show,
    success,
    error,
    info,
    warning,
  }
}
