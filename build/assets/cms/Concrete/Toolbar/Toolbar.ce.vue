<template>
  <div
    id="ccm-page-controls-wrapper"
    :data-theme="resolvedTheme"
    class="w-full fixed top-0 left-0 z-[var(--index-layer-toolbar-wrapper)]"
  >
    <div ref="teleportTarget"></div>
    <div id="ccm-toolbar" class="relative z-[var(--index-layer-toolbar)] flex flex-row justify-between items-center px-6 py-4 bg-base-100 shadow-[inset_-2px_-2px_10px_rgba(0,0,0,0.1)]">
      <div class="flex items-center space-x-1">
        <!-- Logo -->
        <span class="text-lg font-bold mr-4">
          <img :src="logoSrc" alt="Logo" class="h-8 w-auto" />
        </span>

        <!-- Master Collection Exit -->
        <div
            v-if="isMasterCollection"
            :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="ui.toolbar.showTooltips ? 'Exit Edit Defaults' : null"
        >
          <a :href="masterCollectionUrl" class="c-toolbar-button">
            <ArrowLeftIcon class="w-4 h-4" />
            <span v-if="ui.toolbar.showTitles">Exit Edit Defaults</span>
          </a>
        </div>

        <!-- Edit Mode / Edit Page -->
        <template v-if="!pageInUseBySomeoneElse && !isAlias">
          <div
              v-if="isEditMode"
              :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
              :data-tip="ui.toolbar.showTooltips ? 'Exit Edit Mode' : null"
          >
            <a
                :href="checkInUrl"
                class="c-toolbar-button c-toolbar-button-active"
            >
              <PencilIcon class="w-4 h-4" />
              <span v-if="ui.toolbar.showTitles">Exit Edit Mode</span>
            </a>
          </div>
          <div
              v-else-if="canEditPageContents"
              :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
              :data-tip="ui.toolbar.showTooltips ? 'Edit This Page' : null"
          >
            <a
                :href="checkoutUrl"
                class="c-toolbar-button"
                title="Edit This Page"
            >
              <PencilIcon class="w-4 h-4" />
              <span v-if="ui.toolbar.showTitles">Edit Mode</span>
            </a>
          </div>
        </template>

        <!-- Add Content -->
        <div v-if="canEditPageContents && !pageInUseBySomeoneElse"
             :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
             :data-tip="ui.toolbar.showTooltips ? 'Add Content' : null">
          <a :href="addContentUrl" @click.prevent="launchAddPanel" class="c-toolbar-button">
            <PlusIcon class="w-4 h-4" />
            <span v-if="ui.toolbar.showTitles">Add</span>
          </a>
        </div>

        <!-- Page Settings -->
        <div
            v-if="canEditPageSettings"
            :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="ui.toolbar.showTooltips ? 'Page Settings' : null"
        >
          <a
              href="#"
              @click.prevent="launchPageSettings"
              class="c-toolbar-button"
          >
            <Cog6ToothIcon class="w-4 h-4" />
            <span v-if="ui.toolbar.showTitles">Settings</span>
          </a>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <Search @search="handleSearch" />
        <HelpButton :help-url="helpUrl" />


        <!-- Dashboard -->
        <div
            v-if="canAccessDashboard"
            :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="ui.toolbar.showTooltips ? 'Dashboard' : null"
        >
          <a :href="dashboardUrl" class="c-toolbar-button">
            <DashboardIcon class="w-4 h-4" />
            <span v-if="ui.toolbar.showTitles">Dashboard</span>
          </a>
        </div>

        <!-- Sitemap -->
        <div
            v-if="canViewSitemap"
            :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="ui.toolbar.showTooltips ? 'Pages' : null"
        >
          <a href="#" @click.prevent="launchSitemap" class="c-toolbar-button">
            <SitemapIcon class="w-4 h-4" />
            <span v-if="ui.toolbar.showTitles">Pages</span>
          </a>
        </div>

        <!-- User Menu -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <button type="button" class="c-toolbar-button">
              <div class="avatar">
                <div class="w-6 h-6 rounded-full ring ring-base-300 ring-offset-base-100 ring-offset-1">
                  <img src="https://robohash.org/user123.png?size=48x48" alt="User Avatar" />
                </div>
              </div>
              <svg class="w-4 h-4 text-base-content" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent class="w-52">
            <DropdownMenuItem as-child>
              <a href="/waiting-for-me">Waiting For Me</a>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
              <a href="/private-messages">Private Messages</a>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <a href="/profile/edit">Edit Profile</a>
            </DropdownMenuItem>
            <DropdownMenuItem as-child>
              <a href="/profile/picture">Profile Picture</a>
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
              <a href="/logout">Sign Out</a>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
    <FloatingPanelGroup
      :backdrop-to="ui.menuContainer ?? 'body'"
      backdrop-class="bg-concrete-backdrop-bg z-[var(--index-layer-panel-backdrop)]"
    >
      <PageFloatingPanel
        v-model:open="pageSettingsOpen"
        :permissions="pageSettingsPermissions"
        :page-id="pageSettingsPageId"
        :loading="pageSettingsLoading"
        :error="pageSettingsError"
      />
      <AddFloatingPanel
        v-model:open="addPanelOpen"
        :loading="addPanelLoading"
        :error="addPanelError"
        :default-tab="addPanelDefaultTab"
        :block-sets="addPanelBlockSets"
      />
    </FloatingPanelGroup>
  </div>
</template>

<script setup lang="ts">

import {
  ArrowLeftIcon,
  PencilIcon,
  Cog6ToothIcon,
  PlusIcon,
} from '@heroicons/vue/24/outline'
import {
  LayoutDashboard as DashboardIcon,
  Map as SitemapIcon,
} from 'lucide-vue-next'
import { computed, ref, onMounted, useTemplateRef } from 'vue'
import Search from './Search/Search.vue'
import HelpButton from "./Button/HelpButton.vue";
import PageFloatingPanel from './FloatingPanel/PageFloatingPanel.vue'
import AddFloatingPanel from './FloatingPanel/AddFloatingPanel.vue'
import {
  FloatingPanelGroup,
  useUiStore,
  useAjax,
  useFloatingPanelsStore,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator
} from '@concretecms/backendui'
const ui = useUiStore()
const floatingPanels = useFloatingPanelsStore()
const { request } = useAjax()

const props = defineProps({
  logoSrc: String,
  isEditMode: Boolean,
  isMasterCollection: Boolean,
  pageInUseBySomeoneElse: Boolean,
  canEditPageContents: Boolean,
  canEditPageSettings: Boolean,
  canAccessDashboard: Boolean,
  canViewSitemap: Boolean,
  isAlias: Boolean,
  masterCollectionUrl: String,
  checkInUrl: String,
  checkoutUrl: String,
  addContentUrl: String,
  dashboardUrl: String,
  sitemapUrl: String,
  helpUrl: String,
  colorScheme: { type: String, default: 'auto' },
})


const resolvedTheme = ref('light')
const teleportTarget = useTemplateRef('teleportTarget')
const pageSettingsPanelId = 'toolbar:page-settings'
const addPanelId = 'toolbar:add'
const pageSettingsOpen = computed({
  get: () => floatingPanels.activePanel === pageSettingsPanelId,
  set: (isOpen: boolean) => {
    if (isOpen) {
      floatingPanels.open(pageSettingsPanelId)
      return
    }
    floatingPanels.close(pageSettingsPanelId)
  },
})
const pageSettingsLoading = ref(false)
const pageSettingsError = ref<string | null>(null)
const pageSettingsPermissions = ref<Record<string, boolean> | null>(null)
const pageSettingsPageId = ref<number | null>(null)
const addPanelOpen = computed({
  get: () => floatingPanels.activePanel === addPanelId,
  set: (isOpen: boolean) => {
    if (isOpen) {
      floatingPanels.open(addPanelId)
      return
    }
    floatingPanels.close(addPanelId)
  },
})
const addPanelLoading = ref(false)
const addPanelError = ref<string | null>(null)
const addPanelDefaultTab = ref<'blocks' | 'clipboard' | 'library' | 'layouts'>('blocks')
const addPanelBlockSets = ref<Array<{ name: string, blockTypes: Array<{
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
}> }>>([])

function handleSearch() {

}

onMounted(() => {
  if (props.colorScheme === 'auto') {
    resolvedTheme.value = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  } else {
    resolvedTheme.value = props.colorScheme
  }

  ui.menuContainer = teleportTarget.value ?? 'body'
  document.querySelector('html').classList.add('ccm-toolbar-visible')

})

const launchPageSettings = () => {
  if (pageSettingsLoading.value) {
    return
  }

  if (pageSettingsOpen.value) {
    pageSettingsOpen.value = false
    return
  }

  pageSettingsLoading.value = true
  pageSettingsError.value = null
  pageSettingsPermissions.value = null
  pageSettingsPageId.value = null
  pageSettingsOpen.value = true
  const currentCollectionId = Number((window as any).CCM_CID ?? 0)
  const pageSettingsUrl = currentCollectionId > 0
    ? `/ccm/system/panels/page?cID=${currentCollectionId}`
    : '/ccm/system/panels/page'

  request({
    url: pageSettingsUrl,
    method: 'GET',
    onSuccess: (data: any) => {
      if (data?.error) {
        pageSettingsError.value = data.error
        pageSettingsPermissions.value = null
        pageSettingsPageId.value = null
        return
      }

      pageSettingsPermissions.value = data?.permissions ?? null
      pageSettingsPageId.value = Number.isInteger(data?.pageId) ? data.pageId : null
    },
    onError: () => {
      pageSettingsError.value = 'Unable to load page settings.'
    },
    onComplete: () => {
      pageSettingsLoading.value = false
    },
  })
}

const launchAddPanel = () => {
  if (addPanelLoading.value) {
    return
  }

  if (addPanelOpen.value) {
    addPanelOpen.value = false
    return
  }

  addPanelLoading.value = true
  addPanelError.value = null
  addPanelBlockSets.value = []
  addPanelDefaultTab.value = 'blocks'
  addPanelOpen.value = true
  const currentCollectionId = Number((window as any).CCM_CID ?? 0)
  const addPanelUrl = currentCollectionId > 0
    ? `/ccm/system/panels/add?cID=${currentCollectionId}`
    : '/ccm/system/panels/add'

  request({
    url: addPanelUrl,
    method: 'GET',
    onSuccess: (data: any) => {
      if (data?.error) {
        addPanelError.value = data.error
        addPanelBlockSets.value = []
        return
      }

      const selectedTab = (data?.selectedTab ?? data?.defaultTab ?? 'blocks') as string
      if (selectedTab === 'clipboard') {
        addPanelDefaultTab.value = 'clipboard'
      } else if (selectedTab === 'stacks' || selectedTab === 'containers') {
        addPanelDefaultTab.value = 'layouts'
      } else {
        addPanelDefaultTab.value = 'blocks'
      }
      addPanelBlockSets.value = Array.isArray(data?.blocks?.sets) ? data.blocks.sets : []
    },
    onError: () => {
      addPanelError.value = 'Unable to load add panel.'
      addPanelBlockSets.value = []
    },
    onComplete: () => {
      addPanelLoading.value = false
    },
  })
}

const launchSitemap = () => {
  console.log('Launch Sitemap Panel')
}
</script>

<style>
</style>
