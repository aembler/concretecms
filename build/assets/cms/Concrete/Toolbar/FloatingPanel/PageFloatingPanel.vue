<script setup lang="ts">
import { computed } from 'vue'
import {
  FloatingPanel,
  FloatingPanelHeader,
  FloatingPanelMenuItem,
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
  permissions?: Partial<PagePermissions> | null
  pageId?: number | null
  loading?: boolean
  error?: string | null
}>(), {
  open: false,
  permissions: null,
  pageId: null,
  loading: false,
  error: null,
})

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
}>()

const modelOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
})

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
  ...(props.permissions ?? {}),
}))

const withPageId = (path: string): string => {
  if (!props.pageId) {
    return path
  }

  const separator = path.includes('?') ? '&' : '?'
  return `${path}${separator}cID=${props.pageId}`
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
</script>

<template>
  <FloatingPanel
    v-model:open="modelOpen"
    compact-width="min(92vw, 34rem)"
    teleport
    show-backdrop
    backdrop-class="bg-concrete-backdrop-bg z-[var(--index-layer-panel-backdrop)]"
    class="fixed left-6 top-[4.85rem] z-[var(--index-layer-panel)] justify-start"
    menu-class="max-h-[70vh] overflow-y-auto pr-1"
  >
    <FloatingPanelHeader
      title="Page Settings"
      description="Manage this page with role-aware controls."
    />

    <div v-if="loading" class="px-3 py-3 text-sm text-slate-600">Loading page controls...</div>
    <div v-else-if="error" class="px-3 py-3 rounded-lg bg-error/10 text-error text-sm">
      {{ error }}
    </div>
    <div v-else-if="visibleGroups.length === 0" class="px-3 py-3 text-sm text-slate-500">
      No page settings are available for your current permissions.
    </div>
    <template v-else>
      <div
        v-for="group in visibleGroups"
        :key="group.label"
        class="mb-2"
      >
        <div class="px-3 pb-2 pt-1 text-[11px] uppercase tracking-[0.14em] text-slate-500">{{ group.label }}</div>
        <FloatingPanelMenuItem
          v-for="item in group.items"
          :key="item.key"
          variant="detail"
          as="a"
          :href="item.href"
          :class="item.class"
          @click="modelOpen = false"
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
    </template>
  </FloatingPanel>
</template>
