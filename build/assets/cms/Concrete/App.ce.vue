<template>
  <div class="antialiased font-[Open_Sans]">
    <Toolbar
        :logo-src="toolbarLogoSrc"
        :is-edit-mode="toolbarIsEditMode"
        :is-master-collection="toolbarIsMasterCollection"
        :page-in-use-by-someone-else="toolbarPageInUseBySomeoneElse"
        :can-edit-page-contents="toolbarCanEditPageContents"
        :can-edit-page-settings="toolbarCanEditPageSettings"
        :can-access-dashboard="toolbarCanAccessDashboard"
        :can-view-sitemap="toolbarCanViewSitemap"
        :is-alias="toolbarIsAlias"
        :master-collection-url="toolbarMasterCollectionUrl"
        :check-in-url="toolbarCheckInUrl"
        :checkout-url="toolbarCheckoutUrl"
        :add-content-url="toolbarAddContentUrl"
        :dashboard-url="toolbarDashboardUrl"
        :sitemap-url="toolbarSitemapUrl"
        :help-url="toolbarHelpUrl"
        :color-scheme="toolbarColorScheme"
    />
    <slot />
  </div>
</template>

<script setup>
import Toolbar from './Toolbar/Toolbar.ce.vue'

// Define all the props explicitly
const props = defineProps({
  toolbarLogoSrc: String,
  toolbarIsEditMode: Boolean,
  toolbarIsMasterCollection: Boolean,
  toolbarPageInUseBySomeoneElse: Boolean,
  toolbarCanEditPageContents: Boolean,
  toolbarCanEditPageSettings: Boolean,
  toolbarCanAccessDashboard: Boolean,
  toolbarCanViewSitemap: Boolean,
  toolbarShowTitles: Boolean,
  toolbarShowTooltips: Boolean,
  toolbarLargeFont: Boolean,
  toolbarIsAlias: Boolean,
  toolbarMasterCollectionUrl: String,
  toolbarCheckInUrl: String,
  toolbarCheckoutUrl: String,
  toolbarAddContentUrl: String,
  toolbarDashboardUrl: String,
  toolbarSitemapUrl: String,
  toolbarHelpUrl: String,
  toolbarColorScheme: {
    type: String,
    default: 'auto'
  }
})

import { useUiStore } from '@concretecms/backendui'
const ui = useUiStore()

ui.toolbar.showTooltips = props.toolbarShowTooltips
ui.toolbar.showTitles = props.toolbarShowTitles
ui.toolbar.useLargeFont = props.toolbarLargeFont

</script>
