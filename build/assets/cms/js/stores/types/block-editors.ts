import type { AddBlockTargetRef } from './page-operations'

export type BlockEditorProps = Record<string, unknown>

export type BlockTypeEditor = {
  component: string
  componentProps?: BlockEditorProps
} | null

export type BlockTypeEditors = {
  add?: BlockTypeEditor
  edit?: BlockTypeEditor
}

type BlockEditorContextBase = {
  editor: NonNullable<BlockTypeEditor>
  pageId: string | number
  areaHandle: string
  blockTypeId?: number
}

export type AddBlockEditorContext = BlockEditorContextBase & {
  mode: 'add'
  operation: {
    blockTypeId: number
    addTarget: AddBlockTargetRef
    ignoreContainer?: boolean
  }
}

export type EditBlockEditorContext = BlockEditorContextBase & {
  mode: 'edit'
  operation: {
    blockId: string | number
    contentHtml?: string | null
    contentEl?: HTMLElement | null
  }
}

export type BlockEditorContext = AddBlockEditorContext | EditBlockEditorContext
