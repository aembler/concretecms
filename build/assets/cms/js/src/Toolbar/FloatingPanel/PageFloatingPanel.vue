<script setup lang="ts" xmlns="http://www.w3.org/1999/html">
import { computed, ref, watch } from 'vue'
import {
  FloatingPanel,
  FloatingPanelBody,
  FloatingPanelContent,
  FloatingPanelHeader,
  FloatingPanelMenu,
  FloatingPanelMenuTitle,
  FloatingPanelMenuItem,
  Toast,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
  useAjax,
} from '@concretecms/backendui'
import {
  PaintBrushIcon,
  CircleStackIcon,
  Cog8ToothIcon,
  EyeIcon,
  KeyIcon,
  MagnifyingGlassIcon,
  MapPinIcon,
  PencilSquareIcon,
  ShieldCheckIcon,
  TrashIcon,
  UserIcon,
} from '@heroicons/vue/24/outline'
import CacheSettingsContent from './Page/Content/CacheSettingsContent.vue'

type PermissionKey =
  | 'composer'
  | 'design'
  | 'seo'
  | 'location'
  | 'attributes'
  | 'caching'
  | 'permissions'
  | 'versions'
  | 'mobilePreview'
  | 'viewAsUser'
  | 'delete'

type PagePermissions = Record<PermissionKey, boolean>

const props = withDefaults(defineProps<{
  open?: boolean
}>(), {
  open: false,
})

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
}>()
const { request } = useAjax()

const modelOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
})

type CachePanelPayload = {
  pageId: number | null
  global: {
    cacheEnabled: boolean
    mode: string
    modeLabel: string
    lifetimeMode: string
    lifetimeLabel: string
  }
  form: {
    cacheMode: string
    lifetimeMode: string
    customLifetimeMinutes: number | null
  }
  status: {
    state: 'cached' | 'unknown' | 'not_cached'
    message: string
    expiresAt?: string | null
    canPurge: boolean
  }
  actions: {
    submitUrl: string
    purgeUrl: string
  }
}

const cacheSettingsLoading = ref(false)
const cacheSettingsError = ref<string | null>(null)
const cacheSettingsData = ref<CachePanelPayload | null>(null)
const isExpanded = ref(false)
const activeContent = ref<string | null>(null)
const toastOpen = ref(false)
const toastTitle = ref('Page Updated')
const toastMessage = ref('Full page caching settings saved.')
const pagePermissions = ref<Partial<PagePermissions> | null>(null)
const pageId = ref<number | null>(null)
const hasLoaded = ref(false)

const fallbackPermissions: PagePermissions = {
  composer: false,
  design: false,
  seo: false,
  location: false,
  attributes: false,
  caching: false,
  permissions: false,
  versions: false,
  mobilePreview: false,
  viewAsUser: false,
  delete: false,
}

const resolvedPermissions = computed<PagePermissions>(() => ({
  ...fallbackPermissions,
  ...(pagePermissions.value ?? {}),
}))

const withPageId = (path: string): string => {
  if (!pageId.value) {
    return path
  }

  const separator = path.includes('?') ? '&' : '?'
  return `${path}${separator}cID=${pageId.value}`
}

const menuGroups = computed(() => [
  {
    label: 'Page Basics',
    items: [
      {
        key: 'composer' as PermissionKey,
        title: 'Composer',
        description: 'Update composer content and page defaults.',
        href: withPageId('/ccm/system/panels/details/page/composer'),
        icon: PencilSquareIcon,
      },
      {
        key: 'design' as PermissionKey,
        title: 'Design',
        description: 'Edit theme, template, and page presentation.',
        href: withPageId('/ccm/system/panels/page/design'),
        icon: PaintBrushIcon,
      },
      {
        key: 'seo' as PermissionKey,
        title: 'SEO',
        description: 'Manage titles, metadata, and search details.',
        href: withPageId('/ccm/system/panels/details/page/seo'),
        icon: MagnifyingGlassIcon,
      },
      {
        key: 'location' as PermissionKey,
        title: 'Location',
        description: 'Adjust page path and URL placement.',
        href: withPageId('/ccm/system/panels/details/page/location'),
        icon: MapPinIcon,
      },
    ],
  },
  {
    label: 'Management',
    items: [
      {
        key: 'attributes' as PermissionKey,
        title: 'Attributes',
        description: 'Set custom page attributes and metadata.',
        href: withPageId('/ccm/system/panels/page/attributes'),
        icon: CircleStackIcon,
      },
      {
        key: 'caching' as PermissionKey,
        title: 'Caching',
        description: 'Tune cache behavior and delivery settings.',
        href: withPageId('/ccm/system/panels/details/page/caching'),
        icon: Cog8ToothIcon,
      },
      {
        key: 'permissions' as PermissionKey,
        title: 'Permissions',
        description: 'Control access to this page and actions.',
        href: withPageId('/ccm/system/panels/details/page/permissions'),
        icon: ShieldCheckIcon,
      },
      {
        key: 'versions' as PermissionKey,
        title: 'Versions',
        description: 'Review history and restore prior states.',
        href: withPageId('/ccm/system/panels/page/versions'),
        icon: KeyIcon,
      },
      {
        key: 'mobilePreview' as PermissionKey,
        title: 'Mobile Preview',
        description: 'Preview this page across device sizes.',
        href: withPageId('/ccm/system/panels/page/devices'),
        icon: EyeIcon,
      },
      {
        key: 'viewAsUser' as PermissionKey,
        title: 'View as User',
        description: 'Preview page behavior for another user.',
        href: withPageId('/ccm/system/panels/page/preview_as_user'),
        icon: UserIcon,
      },
      {
        key: 'delete' as PermissionKey,
        title: 'Delete Page',
        description: 'Permanently remove this page.',
        href: withPageId('/ccm/system/dialogs/page/delete'),
        icon: TrashIcon,
        class: 'text-error hover:bg-error/10 hover:border-error/40',
      },
    ],
  },
])

const visibleGroups = computed(() => menuGroups.value
  .map((group) => ({
    ...group,
    items: group.items.filter((item) => resolvedPermissions.value[item.key]),
  }))
  .filter((group) => group.items.length > 0))

const visibleItems = computed(() => visibleGroups.value.flatMap((group) => group.items))

watch(
  () => modelOpen.value,
  (isOpen) => {
    if (!isOpen) {
      isExpanded.value = false
      activeContent.value = null
    }
  },
)

function loadPanelData() {
  if (hasLoaded.value) {
    return
  }
  hasLoaded.value = true

  const currentCollectionId = Number((window as any).CCM_CID ?? 0)
  const panelUrl = currentCollectionId > 0
    ? `/ccm/system/panels/page?cID=${currentCollectionId}`
    : '/ccm/system/panels/page'

  request({
    url: panelUrl,
    method: 'GET',
    onSuccess: (data: any) => {
      pagePermissions.value = data?.permissions ?? null
      pageId.value = Number.isInteger(data?.pageId) ? data.pageId : null
    },
  })
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    loadPanelData()
  }
})

function loadCacheSettings() {
  if (cacheSettingsLoading.value) {
    activeContent.value = 'caching'
    isExpanded.value = true
    return
  }

  const cacheUrl = withPageId('/ccm/system/panels/details/page/caching')
  cacheSettingsLoading.value = true
  cacheSettingsError.value = null
  activeContent.value = 'caching'
  isExpanded.value = true

  request({
    url: cacheUrl,
    method: 'GET',
    onSuccess: (data: any) => {
      if (data?.error) {
        cacheSettingsError.value = data.error
        cacheSettingsData.value = null
        return
      }

      cacheSettingsData.value = data as CachePanelPayload
    },
    onComplete: () => {
      cacheSettingsLoading.value = false
    },
  })
}

function handleMenuItemClick(
  itemKey: PermissionKey,
  event: MouseEvent,
) {
  if (itemKey === 'caching') {
    event.preventDefault()
    loadCacheSettings()
    return
  }

  modelOpen.value = false
}

function handleCacheSettingsSaved(payload: { title?: string, message?: string }) {
  toastTitle.value = payload?.title || 'Page Updated'
  toastMessage.value = payload?.message || 'Full page caching settings saved.'
  toastOpen.value = false
  toastOpen.value = true
  modelOpen.value = false
}
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
      <FloatingPanelHeader
        title="Page Settings"
        description="Manage this page with role-aware controls."
        :closeable="true"
        :expandable="false"
      />
      </template>

      <template #menu>
        <FloatingPanelBody>
          <FloatingPanelMenu>
            <div
                v-for="group in visibleGroups"
                :key="group.label"
                class="mb-2"
            >
              <FloatingPanelMenuTitle>{{ group.label }}</FloatingPanelMenuTitle>
              <FloatingPanelMenuItem
                  v-for="item in group.items"
                  :key="item.key"
                  variant="detail"
                  as="a"
                  :href="item.href"
                  :class="item.class"
                  @click="handleMenuItemClick(item.key, $event)"
              >
                <template #icon>
                  <component :is="item.icon" class="w-5 h-5" />
                </template>
                {{ item.title }}
                <template #description>
                  {{ item.description }}
                </template>
              </FloatingPanelMenuItem>
            </div>
          </FloatingPanelMenu>
        </FloatingPanelBody>
      </template>

      <template #detail>
        <FloatingPanelBody>
          <CacheSettingsContent
            v-if="activeContent === 'caching'"
            :loading="cacheSettingsLoading"
            :error="cacheSettingsError"
            :data="cacheSettingsData"
            @saved="handleCacheSettingsSaved"
          />
        <div v-else class="p-6 text-sm text-slate-500">
          Select a page setting to continue.
        </div>
        </FloatingPanelBody>
      </template>
    </FloatingPanel>

    <ToastProvider :duration="3000" swipe-direction="right">
      <Toast :open="toastOpen" variant="success" @update:open="toastOpen = $event">
        <div class="grid gap-1">
          <ToastTitle>{{ toastTitle }}</ToastTitle>
          <ToastDescription>{{ toastMessage }}</ToastDescription>
        </div>
        <ToastClose />
      </Toast>
      <ToastViewport />
    </ToastProvider>
  </div>
</template>
