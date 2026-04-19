import type { AddBlockTargetRef } from '../types'
import {Component, defineAsyncComponent} from "vue";

export type BlockEditorProps = Record<string, unknown>

export type BlockTypeEditor = {
  component: string
  componentProps?: BlockEditorProps
} | null

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

export type BlockEditorMeta = {
  // Controls what happens to the block's on-page slot content while the editor is open.
  // - preserve: keep rendering it in place
  // - hide: hide it while editing
  // - wrap: reserved for editors that want to render around the existing content
  pageContentMode: 'preserve' | 'hide' | 'wrap'
  // Describes where the editor actually renders.
  // - dialog: overlay/modal style editor that doesn't participate in page layout
  // - page: inline/page-mounted editor that should inherit page layout context
  placement: 'dialog' | 'page'
  // Controls what view of the page content the editor wants from Block.ce.vue.
  // - none: editor doesn't need page content
  // - html: pass a snapshot of the current content HTML
  // - element: pass the live content element
  // - slot: reserved for editors that want to place the page content themselves
  editorContentSource: 'none' | 'html' | 'element' | 'slot'
}

export type BlockEditorModule = {
  // Vue SFC modules expose their component as the default export.
  default: Component
  // Each editor module must describe how it wants Block.ce.vue to treat page content.
  blockEditorMeta: BlockEditorMeta
}
