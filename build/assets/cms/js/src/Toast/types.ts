import {OperationStatus} from "../App/types"

export type ToastVariant = 'success' | 'error' | 'info' | 'warning'

export type ToastOperation = {
  id: string
  type: 'toast.show'
  status: OperationStatus
  title: string
  message: string
  variant?: ToastVariant
  duration?: number
}
