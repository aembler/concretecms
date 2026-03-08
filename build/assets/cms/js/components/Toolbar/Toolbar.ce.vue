<template>
  <div
    id="ccm-page-controls-wrapper"
    :data-theme="resolvedTheme"
    class="w-full fixed top-0 left-0 z-[var(--index-layer-toolbar-wrapper)]"
  >
    <div ref="teleportTarget"></div>
    <div ref="toastTeleportTarget"></div>
    <div id="ccm-toolbar" class="relative z-[var(--index-layer-toolbar)] flex flex-row justify-between items-center px-6 py-4 bg-base-100 shadow-[inset_-2px_-2px_10px_rgba(0,0,0,0.1)]">
      <div class="flex items-center space-x-1">
        <!-- Logo -->
        <span class="text-lg font-bold mr-4">
          <img :src="logoSrc" alt="Logo" class="h-8 w-auto" />
        </span>

        <!-- Master Collection Exit -->
        <div
            v-if="isMasterCollection"
            :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="concreteUi.toolbar.showTooltips ? 'Exit Edit Defaults' : null"
        >
          <a :href="masterCollectionUrl" class="c-toolbar-button">
            <ArrowLeftIcon class="w-4 h-4" />
            <span v-if="concreteUi.toolbar.showTitles">Exit Edit Defaults</span>
          </a>
        </div>

        <!-- Edit Mode / Edit Page -->
        <template v-if="!pageInUseBySomeoneElse && !isAlias">
          <div
              v-if="isEditMode"
              :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
              :data-tip="concreteUi.toolbar.showTooltips ? 'Exit Edit Mode' : null"
          >
            <a
                :href="checkInUrl"
                @click.prevent="handleExitEditMode"
                class="c-toolbar-button c-toolbar-button-active"
            >
              <PencilIcon class="w-4 h-4" />
              <span v-if="concreteUi.toolbar.showTitles">Exit Edit Mode</span>
            </a>
          </div>
          <div
              v-else-if="canEditPageContents"
              :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
              :data-tip="concreteUi.toolbar.showTooltips ? 'Edit This Page' : null"
          >
            <a
                :href="checkoutUrl"
                class="c-toolbar-button"
                title="Edit This Page"
            >
              <PencilIcon class="w-4 h-4" />
              <span v-if="concreteUi.toolbar.showTitles">Edit Mode</span>
            </a>
          </div>
        </template>

        <!-- Add Content -->
        <div v-if="canEditPageContents && !pageInUseBySomeoneElse"
             :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
             :data-tip="concreteUi.toolbar.showTooltips ? 'Add Content' : null">
          <a :href="addContentUrl" @click.prevent="launchAddPanel" class="c-toolbar-button">
            <PlusIcon class="w-4 h-4" />
            <span v-if="concreteUi.toolbar.showTitles">Add</span>
          </a>
        </div>

        <!-- Page Settings -->
        <div
            v-if="canEditPageSettings"
            :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="concreteUi.toolbar.showTooltips ? 'Page Settings' : null"
        >
          <a
              href="#"
              @click.prevent="launchPageSettings"
              class="c-toolbar-button"
          >
            <Cog6ToothIcon class="w-4 h-4" />
            <span v-if="concreteUi.toolbar.showTitles">Settings</span>
          </a>
        </div>
      </div>

      <div class="flex items-center space-x-2">
        <Search @search="handleSearch" />
        <HelpButton :help-url="helpUrl" />


        <!-- Dashboard -->
        <div
            v-if="canAccessDashboard"
            :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="concreteUi.toolbar.showTooltips ? 'Dashboard' : null"
        >
          <a :href="dashboardUrl" class="c-toolbar-button">
            <DashboardIcon class="w-4 h-4" />
            <span v-if="concreteUi.toolbar.showTitles">Dashboard</span>
          </a>
        </div>

        <!-- Sitemap -->
        <div
            v-if="canViewSitemap"
            :class="[concreteUi.toolbar.showTooltips && 'tooltip tooltip-bottom']"
            :data-tip="concreteUi.toolbar.showTooltips ? 'Pages' : null"
        >
          <a href="#" @click.prevent="launchSitemap" class="c-toolbar-button">
            <SitemapIcon class="w-4 h-4" />
            <span v-if="concreteUi.toolbar.showTitles">Pages</span>
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
      :backdrop-class="floatingPanelBackdropClass"
    >
      <PageFloatingPanel
        :open="pageSettingsOpen"
        @update:open="setPanelOpen(pageSettingsPanelId, $event)"
      />
      <CheckInFloatingPanel
        :open="checkInOpen"
        :page-id="pageId"
        @update:open="setPanelOpen(checkInPanelId, $event)"
      />
      <AddFloatingPanel
        :open="addPanelOpen"
        @update:open="setPanelOpen(addPanelId, $event)"
      />
    </FloatingPanelGroup>
    <ToastContainer />
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
import { computed, ref, onMounted, useTemplateRef, watch } from 'vue'
import Search from './Search/Search.vue'
import HelpButton from "./Button/HelpButton.vue";
import ToastContainer from '../Ui/ToastContainer.vue'
import PageFloatingPanel from './FloatingPanel/PageFloatingPanel.vue'
import AddFloatingPanel from './FloatingPanel/AddFloatingPanel.vue'
import CheckInFloatingPanel from './FloatingPanel/CheckInFloatingPanel.vue'
import {
  FloatingPanelGroup,
  useUiStore,
  useFloatingPanelsStore,
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator
} from '@concretecms/backendui'
import { useConcreteUiStore } from '../../stores/concrete-ui'
const ui = useUiStore()
const concreteUi = useConcreteUiStore()
const floatingPanels = useFloatingPanelsStore()

const props = defineProps({
  pageId: Number,
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
  requiresCheckInPanel: Boolean,
  checkoutUrl: String,
  addContentUrl: String,
  dashboardUrl: String,
  sitemapUrl: String,
  helpUrl: String,
  colorScheme: { type: String, default: 'auto' },
})


const resolvedTheme = ref('light')
const teleportTarget = useTemplateRef('teleportTarget')
const toastTeleportTarget = useTemplateRef('toastTeleportTarget')
const pageSettingsPanelId = 'toolbar:page-settings'
const checkInPanelId = 'toolbar:check-in'
const addPanelId = 'toolbar:add'
const pageSettingsOpen = computed(() => floatingPanels.activePanel === pageSettingsPanelId)
const checkInOpen = computed(() => floatingPanels.activePanel === checkInPanelId)
const addPanelOpen = computed(() => floatingPanels.activePanel === addPanelId)
const isAddContentDragActive = computed(() => concreteUi.page.addContentDragActive)
const floatingPanelBackdropClass = computed(() =>
  isAddContentDragActive.value
    ? 'pointer-events-none bg-transparent z-[var(--index-layer-panel-backdrop)]'
    : 'bg-concrete-backdrop-bg z-[var(--index-layer-panel-backdrop)]')

function handleSearch() {

}

function setActivePanel(panelId: string | null) {
  if (panelId) {
    floatingPanels.open(panelId)
    return
  }

  const currentPanel = floatingPanels.activePanel
  if (currentPanel) {
    floatingPanels.close(currentPanel)
  }
}

function togglePanel(panelId: string) {
  if (floatingPanels.activePanel === panelId) {
    setActivePanel(null)
    return
  }
  setActivePanel(panelId)
}

function setPanelOpen(panelId: string, isOpen: boolean) {
  if (isOpen) {
    setActivePanel(panelId)
  } else if (floatingPanels.activePanel === panelId) {
    setActivePanel(null)
  }
}

onMounted(() => {
  if (props.colorScheme === 'auto') {
    resolvedTheme.value = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
  } else {
    resolvedTheme.value = props.colorScheme
  }

  ui.menuContainer = teleportTarget.value ?? 'body'
  concreteUi.toastContainer = toastTeleportTarget.value ?? 'body'
  document.querySelector('html').classList.add('ccm-toolbar-visible')

})

watch(() => addPanelOpen.value, (isOpen) => {
  if (!isOpen) {
    concreteUi.page.addContentDragActive = false
  }
})

const launchPageSettings = () => {
  togglePanel(pageSettingsPanelId)
}

const handleExitEditMode = () => {
  if (!props.requiresCheckInPanel) {
    if (props.checkInUrl) {
      window.location.href = props.checkInUrl
    }
    return
  }

  togglePanel(checkInPanelId)
}

const launchAddPanel = () => {
  togglePanel(addPanelId)
}

const launchSitemap = () => {
  console.log('Launch Sitemap Panel')
}
</script>

<style>
</style>
