<template>
  <div class="flex-none mr-6">
    <a href="/" class="flex items-center gap-2.5">
          <span class="text-lg font-bold mr-2">
            <img :src="logoSrc" alt="Logo" class="h-8 w-auto"/>
          </span>
      <span class="text-lg font-semibold tracking-tight text-base-content">Dashboard</span>
    </a>
  </div>

  <div class="flex-1"></div>
  <div class="flex items-center gap-1">
    <div class="hidden lg:block mr-2">
      <Search/>
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
      <HelpButton :help-url="helpUrl"/>
    </div>

    <div class="hidden sm:block h-5 w-px bg-base-300"></div>

    <div
        class="hidden sm:block tooltip tooltip-bottom"
        data-tip="Add Functionality"
    >
      <DropdownMenu>
        <DropdownMenuTrigger class="outline-none">
          <div
              class="btn btn-ghost btn-sm btn-square text-base-content/60 hover:text-base-content"
              aria-label="Add Functionality"
              title="Add Functionality"
          >
            <PuzzlePieceIcon class="h-4 w-4"/>
          </div>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="mx-6 mt-3 w-40 rounded-xl shadow-2xl z-[var(--index-layer-toolbar-dropdown,415)]">
          <DropdownMenuItem as-child>
            <a :href="extendUrl">Add Functionality</a>
          </DropdownMenuItem>
          <DropdownMenuItem as-child>
            <a :href="extendUpdateUrl">Update Add-Ons</a>
          </DropdownMenuItem>
          <DropdownMenuItem as-child>
            <a :href="extendThemesUrl">Get More Themes</a>
          </DropdownMenuItem>
          <DropdownMenuItem as-child>
            <a :href="addonsUrl">Get More Add-Ons</a>
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <div class="hidden sm:block h-5 w-px bg-base-300"></div>

    <div class="hidden sm:flex tooltip tooltip-bottom" data-tip="System and Settings"><a
        :href="settingsUrl"
        class="btn btn-ghost btn-sm btn-square text-base-content/60 hover:text-base-content"
        aria-label="Settings"
        title="Settings"
    >
      <CogIcon class="h-4 w-4"/>
    </a>
    </div>

    <div class="h-5 w-px bg-base-300"></div>

    <UserMenuButton/>
  </div>

</template>

<script setup>
import {PuzzlePieceIcon} from '@heroicons/vue/24/outline'
import {CogIcon} from '@heroicons/vue/24/outline'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@concretecms/backendui'
import HelpButton from '../../../../cms/js/components/Toolbar/Button/HelpButton.vue'
import Search from '../../../../cms/js/components/Toolbar/Search/Search.vue'
import UserMenuButton from '../../../../cms/js/components/Toolbar/Button/UserMenuButton.vue'

defineProps({
  addonsUrl: {
    type: String,
    required: true,
  },
  extendThemesUrl: {
    type: String,
    required: true,
  },
  extendUpdateUrl: {
    type: String,
    required: true,
  },
  extendUrl: {
    type: String,
    required: true,
  },
  helpUrl: {
    type: String,
    required: true,
  },
  logoSrc: {
    type: String,
    required: true,
  },
  settingsUrl: {
    type: String,
    required: true,
  },
})
</script>
