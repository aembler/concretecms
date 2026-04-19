import { defineAsyncComponent, type Component } from 'vue'
import {BlockEditorModule, BlockEditorMeta} from "./types";

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
    () => import('./DialogEditor.vue') as Promise<BlockEditorModule>
  ),
  ComposableEditor: createEditorDefinition(
    () => import('./ComposableEditor.vue') as Promise<BlockEditorModule>
  ),
  ConcreteBlockContentEditor: createEditorDefinition(
    () => import('../../../../../../../concrete/blocks/content/Editor.vue') as Promise<BlockEditorModule>
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
