export type PageOperationStatus = 'queued' | 'running' | 'done' | 'failed'

export type BlockRef = {
  bID: string | number
  arHandle: string
  cID: string | number
}

export type AddBlockTargetRef = {
  areaId: string | number
  areaHandle: string
  pageId: string | number
  afterBlockId: string | number
  targetIndex?: string | number
  container?: {
    start?: string
    end?: string
  } | null
}

export type PendingAddEditorRequest = {
  id: string
  blockTypeId: number
  blockTypeHandle?: string
  blockTitle?: string
  ignoreContainer?: boolean
  target: AddBlockTargetRef
  editor: {
    component: string
    componentProps?: Record<string, unknown>
  }
}

export type DeleteBlockOperation = {
  id: string
  type: 'block.delete'
  status: PageOperationStatus
  pageBlock: BlockRef
  deleteAll: boolean
}

export type UpdateBlockOperation = {
  id: string
  type: 'block.update'
  status: PageOperationStatus
  originalBlock: BlockRef
  updatedBlock: BlockRef
  replacementHtml?: string
  response?: any
}

export type AddBlockOperation = {
  id: string
  type: 'block.add'
  status: PageOperationStatus
  blockTypeId: number
  blockTypeHandle?: string
  blockTitle?: string
  ignoreContainer?: boolean
  target: AddBlockTargetRef
  response?: any
}

export type ToastVariant = 'success' | 'error' | 'info' | 'warning'

export type ToastOperation = {
  id: string
  type: 'toast.show'
  status: PageOperationStatus
  title: string
  message: string
  variant?: ToastVariant
  duration?: number
}

export type PageOperation = DeleteBlockOperation | UpdateBlockOperation | AddBlockOperation
