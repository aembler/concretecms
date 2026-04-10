<template>
  <Teleport v-if="portalTarget" :to="portalTarget">
    <div class="fixed left-6 right-6 top-[5.25rem] z-[var(--index-layer-panel)]">
      <div class="inline-block max-w-full">
        <FloatingPanel
          v-model:open="isOpen"
          v-model:expanded="isExpanded"
          width="min(92vw, 32rem)"
          height="calc(100vh - 8.5rem)"
          @after-leave="handleAfterLeave"
        >
          <template #header>
            <FloatingPanelHeader
              :closeable="true"
              :expandable="true"
              :title="editorTitle"
              :description="editorDescription"
              class="px-1 mb-4"
            >
              <template #tabs>
                <ComposableEditorTabs
                  v-if="tabs.length > 0"
                  v-model="activeTabId"
                  :tabs="tabs"
                />
              </template>
            </FloatingPanelHeader>
          </template>

          <template #default>
            <FloatingPanelBody>
              <div class="space-y-4 p-5">
                <div v-if="isLoading" class="alert alert-info text-sm">Loading block manifest…</div>

                <div v-else-if="loadError" class="alert alert-error text-sm">{{ loadError }}</div>

                <template v-else-if="manifest">
                  <div class="grid gap-4">
                    <template v-for="element in activeTabChildren" :key="element.key">
                      <ComposableEditorFieldset
                        v-if="element.type === 'fieldset'"
                        :legend="element.legend"
                      >
                        <ComposableEditorFieldRenderer
                          v-for="field in element.fields"
                          :key="field.id"
                          :component="field.component"
                          :field="field.definition"
                          :model-value="String(values[field.id] ?? '')"
                          @update:model-value="updateValue(field.id, $event)"
                        />
                      </ComposableEditorFieldset>

                      <ComposableEditorFieldRenderer
                        v-else-if="element.type === 'field'"
                        :component="element.component"
                        :field="element.definition"
                        :model-value="String(values[element.id] ?? '')"
                        @update:model-value="updateValue(element.id, $event)"
                      />
                    </template>

                    <div class="rounded-box bg-neutral text-neutral-content">
                      <div class="px-4 py-3 text-xs font-bold uppercase tracking-[0.12em] text-neutral-content/70">
                        Current editor values
                      </div>
                      <pre class="overflow-auto px-4 pb-4 text-xs leading-6">{{ formattedValues }}</pre>
                    </div>
                  </div>
                </template>
              </div>
            </FloatingPanelBody>
          </template>

          <template #footer>
            <FloatingPanelFooter class="flex items-center justify-end gap-3">
              <button type="button" class="btn btn-secondary me-auto" :disabled="isSubmitting" @click="handleCancel">Cancel</button>
              <button type="button" class="btn btn-primary" :disabled="isSubmitting || isLoading || Boolean(loadError)" @click="handleSave">Save</button>
            </FloatingPanelFooter>
          </template>
        </FloatingPanel>
      </div>
    </div>
  </Teleport>
</template>

<script lang="ts">
import type { BlockEditorMeta } from '../../../stores/block-editor-registry'

export const blockEditorMeta: BlockEditorMeta = {
  pageContentMode: 'preserve',
  placement: 'page',
  editorContentSource: 'none',
}
</script>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { FloatingPanel, FloatingPanelBody, FloatingPanelFooter, FloatingPanelHeader, useUiStore, useAjax } from '@concretecms/backendui'
import ComposableEditorFieldRenderer from '../../ComposableEditor/FieldRenderer.vue'
import ComposableEditorFieldset from '../../ComposableEditor/Fieldset.vue'
import ComposableEditorTabs from '../../ComposableEditor/Tabs.vue'
import ComposableEditorColorField from '../../ComposableEditor/Field/ComposableEditorColorField.vue'
import ComposableEditorTextField from '../../ComposableEditor/Field/ComposableEditorTextField.vue'
import ComposableEditorTextareaField from '../../ComposableEditor/Field/ComposableEditorTextareaField.vue'
import { useBlockEditorSession } from './useBlockEditorSession'
import type { BlockEditorContext } from '../../../stores/types/block-editors'

type ManifestFieldDefinition = {
  id: string
  type: string
  label: string
  definition: Record<string, unknown>
  fieldType?: {
    handle?: string
    component?: string
    componentProps?: Record<string, unknown>
  } | null
}

type FieldReferenceElement = {
  type: 'fieldref'
  fieldId: string
}

type FieldsetElement = {
  type: 'fieldset'
  name: string
  children: Array<FieldReferenceElement>
}

type TabElement = {
  type: 'tab'
  id: string
  name: string
  children: Array<FieldReferenceElement | FieldsetElement>
}

type ManifestPayload = {
  handle: string
  name: string
  description: string
  fields: Record<string, ManifestFieldDefinition>
  layout: Array<TabElement>
}

type RenderableField = {
  id: string
  definition: ManifestFieldDefinition
  component: any
}

type RenderableLayoutElement =
  | {
      key: string
      type: 'field'
      id: string
      definition: ManifestFieldDefinition
      component: any
    }
  | {
      key: string
      type: 'fieldset'
      legend: string
      fields: Array<RenderableField>
    }

const props = defineProps<{
  context: BlockEditorContext
}>()

const emit = defineEmits<{
  (e: 'updated', payload: { response: any }): void
  (e: 'closed'): void
}>()

const { request } = useAjax()
const uiStore = useUiStore()

const manifest = ref<ManifestPayload | null>(null)
const isLoading = ref(true)
const loadError = ref<string | null>(null)
const activeTabId = ref<string | null>(null)
const values = ref<Record<string, string>>({})
const isOpen = ref(true)
const isExpanded = ref(false)
const { isSubmitting, submit, submitUrl } = useBlockEditorSession(
  computed(() => props.context),
  {
    onUpdated: (payload) => {
      emit('updated', payload)
      isOpen.value = false
    },
  }
)

const portalTarget = computed(() => uiStore.menuContainer ?? null)

const fieldComponents: Record<string, any> = {
  ComposableEditorTextField,
  ComposableEditorTextareaField,
  ComposableEditorColorField,
}

const tabs = computed(() => manifest.value?.layout ?? [])

const editorTitle = computed(() => manifest.value?.name || 'Block Editor')
const editorDescription = computed(() => manifest.value?.description || 'Edit manifest-backed block fields.')

const activeTab = computed(() => {
  if (tabs.value.length === 0) {
    return null
  }

  const matchingTab = tabs.value.find((tab) => tab.id === activeTabId.value)
  return matchingTab ?? tabs.value[0]
})

const activeTabChildren = computed<RenderableLayoutElement[]>(() => {
  const tab = activeTab.value
  if (!tab || !manifest.value) {
    return []
  }

  return tab.children.flatMap((child, index) => {
    if (child.type === 'fieldref') {
      const field = resolveRenderableField(child.fieldId)
      return field ? [{
        key: `${tab.id}.field.${index}.${field.id}`,
        type: 'field' as const,
        id: field.id,
        definition: field.definition,
        component: field.component,
      }] : []
    }

    if (child.type === 'fieldset') {
      const fields = child.children
        .map((fieldRef) => resolveRenderableField(fieldRef.fieldId))
        .filter((field): field is RenderableField => field !== null)

      return [{
        key: `${tab.id}.fieldset.${index}.${child.name || 'fieldset'}`,
        type: 'fieldset' as const,
        legend: child.name || '',
        fields,
      }]
    }

    return []
  })
})

const formattedValues = computed(() => JSON.stringify(values.value, null, 2))
const manifestBlockTypeId = computed(() => {
  if (props.context.mode === 'add') {
    return Number(props.context.operation.blockTypeId || 0)
  }

  return Number(props.context.blockTypeId || 0)
})

function resolveRenderableField(fieldId: string): RenderableField | null {
  const definition = manifest.value?.fields?.[fieldId]
  if (!definition) {
    return null
  }

  const componentName = definition.fieldType?.component ?? ''
  const component = fieldComponents[componentName]
  if (!component) {
    return null
  }

  return {
    id: fieldId,
    definition,
    component,
  }
}

function getInitialFieldValue(field: ManifestFieldDefinition): string {
  const defaultValue = field.definition?.default
  if (typeof defaultValue === 'string') {
    return defaultValue
  }

  return ''
}

function updateValue(fieldId: string, value: string) {
  values.value = {
    ...values.value,
    [fieldId]: value,
  }
}

function handleCancel() {
  isOpen.value = false
}

function handleSave() {
  if (!manifest.value) {
    return
  }

  const body = new FormData()
  for (const [fieldId, value] of Object.entries(values.value)) {
    body.set(fieldId, value)
  }

  submit({
    url: submitUrl.value,
    body,
  })
}

function hydrateManifest(data: ManifestPayload) {
  manifest.value = data
  activeTabId.value = data.layout[0]?.id ?? null

  const nextValues: Record<string, string> = {}
  for (const field of Object.values(data.fields ?? {})) {
    nextValues[field.id] = getInitialFieldValue(field)
  }

  values.value = nextValues
}

function handleAfterLeave() {
  emit('closed')
}

onMounted(() => {
  request({
    url: CCM_DISPATCHER_FILENAME + `/ccm/block_types/manifest/${manifestBlockTypeId.value}`,
    method: 'GET',
    onSuccess: (data) => {
      hydrateManifest(data as ManifestPayload)
      loadError.value = null
    },
    onError: () => {
      loadError.value = 'Unable to load the block manifest.'
    },
    onComplete: () => {
      isLoading.value = false
    },
  })
})
</script>
