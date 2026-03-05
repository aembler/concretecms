export type BlockEditorProps = Record<string, unknown>

export type BlockTypeEditor = {
  component: string
  componentProps?: BlockEditorProps
} | null

export type BlockTypeEditors = {
  add?: BlockTypeEditor
  edit?: BlockTypeEditor
}
