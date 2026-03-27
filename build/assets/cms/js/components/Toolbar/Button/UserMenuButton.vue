<template>
  <DropdownMenu>
    <DropdownMenuTrigger>
      <div class="c-toolbar-button">
        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-xs font-semibold text-white shadow-sm">
          A
        </div>
      </div>
    </DropdownMenuTrigger>
    <DropdownMenuContent class="mx-6 mt-3 w-52 rounded-xl shadow-2xl z-[var(--index-layer-toolbar-dropdown,415)]">
      <DropdownMenuItem as-child class="mb-1 -mx-2 -mt-2 flex flex-col border-b border-base-300 py-3">
        <div>
          <span class="text-sm font-semibold leading-tight text-base-content">Admin User</span>
          <span class="text-xs font-normal text-base-content/40">admin@example.com</span>
        </div>
      </DropdownMenuItem>
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
</template>

<script setup lang="ts">
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@concretecms/backendui'
</script>
