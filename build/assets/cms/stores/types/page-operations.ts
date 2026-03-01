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
}

export type DeleteBlockOperation = {
  id: string
  type: 'block.delete'
  status: PageOperationStatus
  pageBlock: BlockRef
  deleteAction: string
  deleteAllAction: string
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
  target: AddBlockTargetRef
  response?: any
}

export type PageOperation = DeleteBlockOperation | UpdateBlockOperation | AddBlockOperation
