<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  FloatingPanel,
  FloatingPanelBackdrop,
  FloatingPanelMenuItem,
  FloatingPanelSearch,
  useUiStore,
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
  CalendarDaysIcon,
  MapPinIcon,
  TableCellsIcon,
  Square3Stack3DIcon,
  DocumentDuplicateIcon,
  PlusCircleIcon,
  Bars3BottomLeftIcon,
  XMarkIcon,
  ChevronRightIcon,
} from '@heroicons/vue/24/outline'

type AddTabId = 'blocks' | 'clipboard' | 'library' | 'layouts'

type BlockType = {
  id: number
  handle: string
  name: string
  description?: string
}

type BlockSet = {
  name: string
  blockTypes: BlockType[]
}

type BlockSearchRecord = {
  setName: string
  name: string
  handle: string
  description: string
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
const ui = useUiStore()

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
      handle: blockType.handle,
      description: blockType.description ?? '',
      blockType,
    }))))

const { items: filteredBlockRecords } = useFuzzySearch(blockSearchRecords, searchKeywords, {
  keys: ['setName', 'name', 'handle', 'description'],
  threshold: 0.34,
  minQueryLengthToSearch: 2,
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
  keys: ['name', 'meta'],
  threshold: 0.34,
  minQueryLengthToSearch: 2,
  debounceMs: 100,
})

const { items: filteredLibraryItems } = useFuzzySearch(() => libraryItems, searchKeywords, {
  keys: ['name', 'type'],
  threshold: 0.34,
  minQueryLengthToSearch: 2,
  debounceMs: 100,
})

const { items: filteredLayoutItems } = useFuzzySearch(() => layoutItems, searchKeywords, {
  keys: ['name', 'detail'],
  threshold: 0.34,
  minQueryLengthToSearch: 2,
  debounceMs: 100,
})

function iconForBlockHandle(handle: string) {
  const normalized = handle.toLowerCase()
  if (normalized.includes('image') || normalized.includes('gallery')) return PhotoIcon
  if (normalized.includes('content') || normalized.includes('html') || normalized.includes('text')) return DocumentTextIcon
  if (normalized.includes('calendar') || normalized.includes('event')) return CalendarDaysIcon
  if (normalized.includes('page_list') || normalized.includes('list')) return QueueListIcon
  if (normalized.includes('topic') || normalized.includes('conversation') || normalized.includes('form')) return ChatBubbleLeftEllipsisIcon
  if (normalized.includes('map')) return MapPinIcon
  if (normalized.includes('layout') || normalized.includes('container')) return TableCellsIcon
  return PlusCircleIcon
}
</script>

<template>
  <div class="fixed left-6 top-[5.25rem] z-[var(--index-layer-panel)]">
    <FloatingPanel
      v-model:open="modelOpen"
      v-model:expanded="isExpanded"
      width="min(92vw, 32rem)"
      height="calc(100vh - 8.5rem)"
    >
      <template #backdrop>
        <FloatingPanelBackdrop
          :to="ui.menuContainer ?? 'body'"
          class="bg-concrete-backdrop-bg z-[var(--index-layer-panel-backdrop)]"
        />
      </template>

      <template #default>
      <div class="px-1 pb-2">
        <div class="relative flex items-center justify-center gap-1 border-b border-slate-200/70 pb-2 pe-16">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg px-2.5 py-2 text-slate-500 transition hover:text-slate-800"
            :class="activeTab === tab.id ? 'text-slate-900 border-b-2 border-slate-900 -mb-[2px] font-semibold' : 'border-b-2 border-transparent'"
            @click="activeTab = tab.id"
          >
            <component :is="tab.icon" class="h-5 w-5" />
            <span v-if="isExpanded" class="text-sm">{{ tab.label }}</span>
          </button>

          <button
            type="button"
            class="absolute right-10 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-current/80 transition-colors hover:bg-gray-500/10"
            :aria-label="isExpanded ? 'Collapse panel' : 'Expand panel'"
            @click="isExpanded = !isExpanded"
          >
            <ChevronRightIcon class="h-4 w-4 transition-transform duration-300" :class="isExpanded ? 'rotate-180' : ''" />
          </button>

          <button
            type="button"
            class="absolute right-1 top-1/2 inline-flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full text-current/80 transition-colors hover:bg-gray-500/10"
            aria-label="Close panel"
            @click="modelOpen = false"
          >
            <XMarkIcon class="size-6 stroke-[1.25]" />
          </button>
        </div>
        <FloatingPanelSearch
          v-model="searchKeywords"
          :placeholder="searchPlaceholder"
          class="mx-2 mt-3"
        />
      </div>

      <div v-if="loading" class="px-3 py-3 text-sm text-slate-600">Loading add panel contents...</div>
      <div v-else-if="error" class="px-3 py-3 rounded-lg bg-error/10 text-error text-sm">
        {{ error }}
      </div>
      <template v-else-if="activeTab === 'blocks'">
        <template v-if="!isExpanded">
          <div
            v-for="set in filteredBlockSets"
            :key="set.name"
            class="mb-2"
          >
            <div class="px-3 pb-2 pt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">{{ set.name }}</div>
            <FloatingPanelMenuItem
              v-for="blockType in set.blockTypes.slice(0, 6)"
              :key="`${set.name}-${blockType.id}`"
              variant="detail"
              as="a"
              href="#"
              @click.prevent
            >
              <template #icon>
                <component :is="iconForBlockHandle(blockType.handle)" class="w-5 h-5" />
              </template>
              {{ blockType.name }}
              <template #description>
                {{ blockType.description || 'Add this block type to the selected area.' }}
              </template>
            </FloatingPanelMenuItem>
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
                <button
                  v-for="blockType in set.blockTypes"
                  :key="`expanded-${set.name}-${blockType.id}`"
                  type="button"
                  class="rounded-xl border border-slate-200 bg-white px-3 py-3 text-left transition hover:border-primary/40 hover:bg-base-100"
                >
                  <div class="flex items-start gap-3">
                    <component :is="iconForBlockHandle(blockType.handle)" class="h-5 w-5 text-slate-500" />
                    <div class="min-w-0">
                      <div class="truncate text-sm font-semibold text-slate-800">{{ blockType.name }}</div>
                      <div class="mt-1 text-xs text-slate-500 line-clamp-2">
                        {{ blockType.description || 'Drag into an editable area to add this block.' }}
                      </div>
                    </div>
                  </div>
                </button>
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
          <div class="px-3 pb-2 pt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">Clipboard</div>
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
          <div class="px-3 pb-2 pt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">Content Library</div>
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
          <div class="px-3 pb-2 pt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">Layouts & Containers</div>
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
