import type { Operation } from '../Queue/queue'

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

export interface DeleteBlockOperation extends Operation {
  type: 'block.delete'
  pageBlock: BlockRef
  deleteAll: boolean
}

export interface UpdateBlockOperation extends Operation {
  type: 'block.update'
  originalBlock: BlockRef
  updatedBlock: BlockRef
  replacementHtml?: string
  response?: any
}

export interface AddBlockOperation extends Operation {
  type: 'block.add'
  blockTypeId: number
  blockTypeHandle?: string
  blockTitle?: string
  ignoreContainer?: boolean
  target: AddBlockTargetRef
  response?: any
}

export type BlockOperation = DeleteBlockOperation | UpdateBlockOperation | AddBlockOperation
