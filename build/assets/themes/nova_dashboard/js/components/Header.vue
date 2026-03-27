<template>
  <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100/95 backdrop-blur-sm">
    <div class="navbar mx-auto max-w-screen-2xl px-4 lg:px-6">
      <div class="flex-none mr-6">
        <a href="/" class="flex items-center gap-2.5">
          <span class="text-lg font-bold mr-2">
            <img :src="logoSrc" alt="Logo" class="h-8 w-auto" />
          </span>
          <span class="text-lg font-semibold tracking-tight text-base-content">Dashboard</span>
        </a>
      </div>

      <div class="hidden flex-1 lg:flex">
        <ul class="menu menu-horizontal gap-0.5 p-0 text-sm font-medium">
          <li v-for="hub in hubs" :key="hub.id">
            <a :href="hub.menu?.url || '#'" class="c-toolbar-button">
              {{ hub.menu?.title || hub.identifier }}
            </a>
          </li>
        </ul>
      </div>
      <div class="flex items-center gap-1">
        <div class="hidden lg:block mr-2">
          <Search />
        </div>

        <!--
        <button class="btn btn-ghost btn-sm btn-square text-base-content/50 hover:text-base-content">
          <div class="indicator">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="badge badge-xs badge-error indicator-item border-0">18+</span>
          </div>
        </button>
        //-->

        <div class="hidden sm:flex">
          <HelpButton :help-url="helpUrl" />
        </div>

        <div class="h-5 w-px bg-base-300"></div>

        <UserMenuButton />
      </div>
    </div>
  </header>
</template>

<script setup>
import HelpButton from '../../../../cms/js/components/Toolbar/Button/HelpButton.vue'
import Search from '../../../../cms/js/components/Toolbar/Search/Search.vue'
import UserMenuButton from '../../../../cms/js/components/Toolbar/Button/UserMenuButton.vue'

defineProps({
  helpUrl: {
    type: String,
    required: true,
  },
  logoSrc: {
    type: String,
    required: true,
  },
  hubs: {
    type: Array,
    required: true,
  },
})
</script>
