<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  FloatingPanel,
  FloatingPanelHeader,
  FloatingPanelHeaderTabs,
  FloatingPanelMenu,
  FloatingPanelMenuTitle,
  FloatingPanelMenuItem,
  FloatingPanelSearch,
  useFuzzySearch,
} from '@concretecms/backendui'
import {
  PuzzlePieceIcon,
  ClipboardDocumentListIcon,
  BookOpenIcon,
  RectangleGroupIcon,
  PhotoIcon,
  DocumentTextIcon,
  QueueListIcon,
  ChatBubbleLeftEllipsisIcon,
  TableCellsIcon,
  Square3Stack3DIcon,
  DocumentDuplicateIcon,
  Bars3BottomLeftIcon,
} from '@heroicons/vue/24/outline'
import AddBlock from './Add/Block.vue'

type AddTabId = 'blocks' | 'clipboard' | 'library' | 'layouts'

type BlockType = {
  id: number
  handle: string
  name: string
  description?: string
  icon?: {
    type: string
    src?: string
    alt?: string
    className?: string
    svg?: string
  }
}

type BlockSet = {
  name: string
  blockTypes: BlockType[]
}

type BlockSearchRecord = {
  setName: string
  name: string
  blockType: BlockType
}

const props = withDefaults(defineProps<{
  open?: boolean
  loading?: boolean
  error?: string | null
  defaultTab?: AddTabId
  blockSets?: BlockSet[]
}>(), {
  open: false,
  loading: false,
  error: null,
  defaultTab: 'blocks',
  blockSets: () => [],
})

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
}>()

const modelOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
})

const isExpanded = ref(false)
const activeTab = ref<AddTabId>('blocks')
const searchKeywords = ref('')

watch(
  () => props.defaultTab,
  (tab) => {
    if (tab) {
      activeTab.value = tab
    }
  },
  { immediate: true },
)

const tabs = [
  { id: 'blocks' as AddTabId, icon: PuzzlePieceIcon, label: 'Blocks' },
  { id: 'clipboard' as AddTabId, icon: ClipboardDocumentListIcon, label: 'Clipboard' },
  { id: 'library' as AddTabId, icon: BookOpenIcon, label: 'Content Library' },
  { id: 'layouts' as AddTabId, icon: RectangleGroupIcon, label: 'Layouts' },
]

const clipboardItems = [
  { name: 'Hero Block: Spring Promo', meta: 'Updated 12m ago', icon: DocumentDuplicateIcon },
  { name: 'Image + Caption Card', meta: 'Updated 1h ago', icon: PhotoIcon },
  { name: 'Feature List Snippet', meta: 'Updated yesterday', icon: Bars3BottomLeftIcon },
]

const libraryItems = [
  { name: 'Landing Page Intro', type: 'Article', icon: DocumentTextIcon },
  { name: 'Product Launch Overview', type: 'Case Study', icon: QueueListIcon },
  { name: 'Support FAQ Section', type: 'Knowledge Base', icon: ChatBubbleLeftEllipsisIcon },
]

const layoutItems = [
  { name: '3-Column Feature Grid', detail: 'Balanced cards, equal spacing', icon: TableCellsIcon },
  { name: '2-Column Content Split', detail: 'Copy + supporting media', icon: Square3Stack3DIcon },
  { name: 'Sidebar + Main Content', detail: 'Navigation-led layout', icon: RectangleGroupIcon },
]

const visibleBlockSets = computed(() => props.blockSets.filter((set) => set.blockTypes.length > 0))

const searchPlaceholder = computed(() => {
  if (activeTab.value === 'blocks') return 'Search blocks'
  if (activeTab.value === 'clipboard') return 'Search clipboard'
  if (activeTab.value === 'library') return 'Search library'
  return 'Search layouts'
})

const blockSearchRecords = computed<BlockSearchRecord[]>(() =>
  visibleBlockSets.value.flatMap((set) =>
    set.blockTypes.map((blockType) => ({
      setName: set.name,
      name: blockType.name,
      blockType,
    }))))

const { items: filteredBlockRecords } = useFuzzySearch(blockSearchRecords, searchKeywords, {
  keys: ['name'],
  threshold: 0.2,
  minQueryLengthToSearch: 3,
  debounceMs: 100,
})

const filteredBlockSets = computed(() => {
  const hasQuery = searchKeywords.value.trim().length > 0
  if (!hasQuery) {
    return visibleBlockSets.value
  }

  const recordsBySet = new Map<string, BlockType[]>()
  for (const record of filteredBlockRecords.value) {
    const blockTypes = recordsBySet.get(record.setName) ?? []
    blockTypes.push(record.blockType)
    recordsBySet.set(record.setName, blockTypes)
  }

  return visibleBlockSets.value
    .map((set) => ({
      name: set.name,
      blockTypes: recordsBySet.get(set.name) ?? [],
    }))
    .filter((set) => set.blockTypes.length > 0)
})

const { items: filteredClipboardItems } = useFuzzySearch(() => clipboardItems, searchKeywords, {
  keys: ['name'],
  threshold: 0.2,
  minQueryLengthToSearch: 3,
  debounceMs: 100,
})

const { items: filteredLibraryItems } = useFuzzySearch(() => libraryItems, searchKeywords, {
  keys: ['name'],
  threshold: 0.2,
  minQueryLengthToSearch: 3,
  debounceMs: 100,
})

const { items: filteredLayoutItems } = useFuzzySearch(() => layoutItems, searchKeywords, {
  keys: ['name'],
  threshold: 0.2,
  minQueryLengthToSearch: 3,
  debounceMs: 100,
})
</script>

<template>
  <div class="fixed left-6 right-6 top-[5.25rem] z-[var(--index-layer-panel)]">
    <FloatingPanel
      v-model:open="modelOpen"
      v-model:expanded="isExpanded"
      width="min(92vw, 26rem)"
      height="calc(100vh - 8.5rem)"
    >
      <template #header>
        <FloatingPanelHeader :closeable="true" :expandable="true" class="px-1 mb-4">
          <template #tabs>
            <FloatingPanelHeaderTabs
              v-model="activeTab"
              :tabs="tabs"
              :show-labels="isExpanded"
            />
          </template>

          <FloatingPanelSearch
            v-model="searchKeywords"
            :placeholder="searchPlaceholder"
            class="mx-2 mt-3"
          />
        </FloatingPanelHeader>
      </template>

      <template #default>
      <div v-if="loading" class="px-3 py-3 text-sm text-slate-600">Loading add panel contents...</div>
      <div v-else-if="error" class="px-3 py-3 rounded-lg bg-error/10 text-error text-sm">
        {{ error }}
      </div>
      <template v-else-if="activeTab === 'blocks'">
        <template v-if="!isExpanded">
          <div class="px-2 pb-3">
            <div
              v-for="set in filteredBlockSets"
              :key="set.name"
              class="mb-6"
            >
              <h4 class="mb-3 px-2 text-sm font-semibold tracking-wide text-slate-700">{{ set.name }}</h4>
              <div class="grid grid-cols-4 gap-3">
                <AddBlock
                  v-for="blockType in set.blockTypes"
                  :key="`${set.name}-${blockType.id}`"
                  :icon="blockType.icon"
                  :title="blockType.name"
                  :description="blockType.description"
                />
              </div>
            </div>
          </div>
          <div v-if="filteredBlockSets.length === 0" class="px-3 py-4 text-sm text-slate-500">
            No block types match your search.
          </div>
        </template>
        <template v-else>
          <div class="px-2 pb-3">
            <div
              v-for="set in filteredBlockSets"
              :key="`expanded-${set.name}`"
              class="mb-6"
            >
              <h4 class="mb-3 px-2 text-sm font-semibold tracking-wide text-slate-700">{{ set.name }}</h4>
              <div class="grid grid-cols-2 gap-3 xl:grid-cols-3">
                <AddBlock
                  v-for="blockType in set.blockTypes"
                  :key="`expanded-${set.name}-${blockType.id}`"
                  :icon="blockType.icon"
                  :title="blockType.name"
                  :description="blockType.description"
                  :expanded="true"
                />
              </div>
            </div>
            <div v-if="filteredBlockSets.length === 0" class="rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-500">
              No block types match your search.
            </div>
          </div>
        </template>
      </template>
      <template v-else-if="activeTab === 'clipboard'">
        <template v-if="!isExpanded">
          <FloatingPanelMenu>
            <FloatingPanelMenuTitle>Clipboard</FloatingPanelMenuTitle>
            <FloatingPanelMenuItem
              v-for="item in filteredClipboardItems"
              :key="item.name"
              variant="detail"
              as="a"
              href="#"
              @click.prevent
            >
              <template #icon>
                <component :is="item.icon" class="w-5 h-5" />
              </template>
              {{ item.name }}
              <template #description>
                {{ item.meta }}
              </template>
            </FloatingPanelMenuItem>
          </FloatingPanelMenu>
          <div v-if="filteredClipboardItems.length === 0" class="px-3 py-4 text-sm text-slate-500">
            No clipboard items match your search.
          </div>
        </template>
        <template v-else>
          <div class="grid grid-cols-1 gap-3 px-2 pb-3">
            <div
              v-for="item in filteredClipboardItems"
              :key="`expanded-${item.name}`"
              class="rounded-xl border border-slate-200 bg-white px-4 py-3"
            >
              <div class="flex items-start gap-3">
                <component :is="item.icon" class="h-5 w-5 text-slate-500" />
                <div>
                  <div class="text-sm font-semibold text-slate-800">{{ item.name }}</div>
                  <div class="mt-1 text-xs text-slate-500">{{ item.meta }}</div>
                </div>
              </div>
            </div>
            <div v-if="filteredClipboardItems.length === 0" class="rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-500">
              No clipboard items match your search.
            </div>
          </div>
        </template>
      </template>
      <template v-else-if="activeTab === 'library'">
        <template v-if="!isExpanded">
          <FloatingPanelMenu>
            <FloatingPanelMenuTitle>Content Library</FloatingPanelMenuTitle>
            <FloatingPanelMenuItem
              v-for="item in filteredLibraryItems"
              :key="item.name"
              variant="detail"
              as="a"
              href="#"
              @click.prevent
            >
              <template #icon>
                <component :is="item.icon" class="w-5 h-5" />
              </template>
              {{ item.name }}
              <template #description>
                {{ item.type }}
              </template>
            </FloatingPanelMenuItem>
          </FloatingPanelMenu>
          <div v-if="filteredLibraryItems.length === 0" class="px-3 py-4 text-sm text-slate-500">
            No content library items match your search.
          </div>
        </template>
        <template v-else>
          <div class="grid grid-cols-1 gap-3 px-2 pb-3 xl:grid-cols-2">
            <div
              v-for="item in filteredLibraryItems"
              :key="`expanded-${item.name}`"
              class="rounded-xl border border-slate-200 bg-white px-4 py-3"
            >
              <div class="flex items-start gap-3">
                <component :is="item.icon" class="h-5 w-5 text-slate-500" />
                <div>
                  <div class="text-sm font-semibold text-slate-800">{{ item.name }}</div>
                  <div class="mt-1 text-xs text-slate-500">{{ item.type }}</div>
                </div>
              </div>
            </div>
            <div v-if="filteredLibraryItems.length === 0" class="rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-500 xl:col-span-2">
              No content library items match your search.
            </div>
          </div>
        </template>
      </template>
      <template v-else>
        <template v-if="!isExpanded">
          <FloatingPanelMenu>
            <FloatingPanelMenuTitle>Layouts & Containers</FloatingPanelMenuTitle>
            <FloatingPanelMenuItem
              v-for="item in filteredLayoutItems"
              :key="item.name"
              variant="detail"
              as="a"
              href="#"
              @click.prevent
            >
              <template #icon>
                <component :is="item.icon" class="w-5 h-5" />
              </template>
              {{ item.name }}
              <template #description>
                {{ item.detail }}
              </template>
            </FloatingPanelMenuItem>
          </FloatingPanelMenu>
          <div v-if="filteredLayoutItems.length === 0" class="px-3 py-4 text-sm text-slate-500">
            No layouts or containers match your search.
          </div>
        </template>
        <template v-else>
          <div class="grid grid-cols-1 gap-3 px-2 pb-3 xl:grid-cols-2">
            <div
              v-for="item in filteredLayoutItems"
              :key="`expanded-${item.name}`"
              class="rounded-xl border border-slate-200 bg-white px-4 py-3"
            >
              <div class="flex items-start gap-3">
                <component :is="item.icon" class="h-5 w-5 text-slate-500" />
                <div>
                  <div class="text-sm font-semibold text-slate-800">{{ item.name }}</div>
                  <div class="mt-1 text-xs text-slate-500">{{ item.detail }}</div>
                </div>
              </div>
            </div>
            <div v-if="filteredLayoutItems.length === 0" class="rounded-xl border border-slate-200 bg-white px-4 py-4 text-sm text-slate-500 xl:col-span-2">
              No layouts or containers match your search.
            </div>
          </div>
        </template>
      </template>
      </template>
    </FloatingPanel>
  </div>
</template>
