import { defineAsyncComponent, type Component } from 'vue'

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

type BlockEditorLoader = () => Promise<BlockEditorModule>
type BlockEditorDefinition = {
  component: Component
  loadMeta: () => Promise<BlockEditorMeta>
}

const defaultMeta: BlockEditorMeta = {
  // Preserve today's behavior unless an editor explicitly opts into something else.
  pageContentMode: 'preserve',
  placement: 'dialog',
  editorContentSource: 'none',
}

function createEditorDefinition(loader: BlockEditorLoader): BlockEditorDefinition {
  let modulePromise: Promise<BlockEditorModule> | null = null

  const loadModule = async (): Promise<BlockEditorModule> => {
    if (modulePromise === null) {
      modulePromise = loader()
    }

    return await modulePromise
  }

  return {
    component: defineAsyncComponent({
      loader: async () => (await loadModule()).default,
    }),
    loadMeta: async () => {
      const mod = await loadModule()
      return mod.blockEditorMeta ?? defaultMeta
    },
  }
}

const editorDefinitions: Record<string, BlockEditorDefinition> = {
  DialogEditor: createEditorDefinition(
    () => import('../components/Block/Editor/DialogEditor.vue') as Promise<BlockEditorModule>
  ),
  ComposableEditor: createEditorDefinition(
    () => import('../components/Block/Editor/ComposableEditor.vue') as Promise<BlockEditorModule>
  ),
  ConcreteBlockContentEditor: createEditorDefinition(
    () => import('../../../../../concrete/blocks/content/Editor.vue') as Promise<BlockEditorModule>
  ),
}

export function useBlockEditorRegistry() {
  return {
    /**
     * The backend sends editor metadata as JSON:
     * - `component`: string key (example: "DialogEditor")
     * - `componentProps`: plain props object
     *
     * Vue's dynamic `<component :is="...">` can't render that backend string by itself
     * in this setup. It needs a real component definition object at runtime.
     *
     * This registry is the single place where we translate backend `component` strings
     * into concrete Vue component modules so both block edit and add flows resolve
     * editors the same way.
     */
    resolveEditorComponent(componentName?: string | null): Component | null {
      if (!componentName || typeof componentName !== 'string') {
        return null
      }

      return editorDefinitions[componentName]?.component ?? null
    },
    async resolveEditorMeta(componentName?: string | null): Promise<BlockEditorMeta> {
      if (!componentName || typeof componentName !== 'string') {
        return defaultMeta
      }

      const definition = editorDefinitions[componentName]
      if (!definition) {
        return defaultMeta
      }

      return await definition.loadMeta()
    },
    hasEditorComponent(componentName?: string | null): boolean {
      if (!componentName || typeof componentName !== 'string') {
        return false
      }

      return Boolean(editorDefinitions[componentName])
    },
  }
}
