<template>
  <header class="sticky top-0 z-50 border-b border-base-300 bg-base-100/95 backdrop-blur-sm">
    <div class="navbar mx-auto max-w-screen-2xl px-4 lg:px-6">
      <div class="flex-none mr-6">
        <a href="/" class="flex items-center gap-2.5">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-violet-600 to-indigo-600 shadow-sm">
            <svg class="h-4 w-4 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
            </svg>
          </div>
          <span class="text-sm font-semibold tracking-tight text-base-content">Nova Dashboard</span>
        </a>
      </div>

      <div class="hidden flex-1 lg:flex">
        <ul class="menu menu-horizontal gap-0.5 p-0 text-sm font-medium">
          <li>
            <a class="rounded-lg px-3 py-2 text-base-content/70 hover:bg-base-200 hover:text-base-content">
              Overview
            </a>
          </li>
          <li>
            <a class="rounded-lg px-3 py-2 text-base-content/70 hover:bg-base-200 hover:text-base-content">
              Payments
            </a>
          </li>
          <li>
            <a class="rounded-lg px-3 py-2 text-base-content/70 hover:bg-base-200 hover:text-base-content">
              Customers
            </a>
          </li>
          <li>
            <a class="rounded-lg px-3 py-2 text-base-content/70 hover:bg-base-200 hover:text-base-content">
              Reports
            </a>
          </li>
          <li>
            <a class="rounded-lg px-3 py-2 text-base-content/70 hover:bg-base-200 hover:text-base-content">
              Developers
            </a>
          </li>
          <li>
            <a class="flex items-center gap-1 rounded-lg px-3 py-2 text-base-content/70 hover:bg-base-200 hover:text-base-content">
              More
              <svg class="h-3 w-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
              </svg>
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
})
</script>
