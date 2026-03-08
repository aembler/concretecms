import { defineAsyncComponent, type Component } from 'vue'

const blockEditorComponents: Record<string, Component> = {
  DialogEditor: defineAsyncComponent(() => import('../components/Block/Editor/DialogEditor.vue')),
  ComposableEditor: defineAsyncComponent(() => import('../components/Block/Editor/ComposableEditor.vue')),
  ConcreteBlockContentEditor: defineAsyncComponent(() => import ('../../../../../concrete/blocks/content/Editor.vue')),
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

      return blockEditorComponents[componentName] ?? null
    },
    hasEditorComponent(componentName?: string | null): boolean {
      if (!componentName || typeof componentName !== 'string') {
        return false
      }

      return Boolean(blockEditorComponents[componentName])
    },
  }
}
