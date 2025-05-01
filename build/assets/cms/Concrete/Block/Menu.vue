<template>
  <InlineToolbar class="flex-nowrap">
    <InlineToolbarSelect class="select-sm w-auto"></InlineToolbarSelect>
    <InlineToolbarGroup>
      <InlineToolbarButton>
        <PencilIcon class="size-4" />
      </InlineToolbarButton>

      <InlineToolbarButton>
        <ClipboardIcon class="size-4" />
      </InlineToolbarButton>

      <InlineToolbarButton>
        <TrashIcon class="size-4 text-error" />
      </InlineToolbarButton>

      <InlineToolbarSeparator />

      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <DropdownMenuTriggerButton size="sm">
            <EllipsisVerticalIcon class="size-4" />
          </DropdownMenuTriggerButton>
        </DropdownMenuTrigger>
        <DropdownMenuContent>
          <DropdownMenuItem>
            A
          </DropdownMenuItem>
          <DropdownMenuItem>
            B
          </DropdownMenuItem>
          <DropdownMenuItem>
            C
          </DropdownMenuItem>
        </DropdownMenuContent>
      </DropdownMenu>

    </InlineToolbarGroup>
  </InlineToolbar>
</template>

<script setup>
import { PencilIcon, ClipboardIcon, TrashIcon, EllipsisVerticalIcon } from '@heroicons/vue/24/outline'
import {
  InlineToolbar,
  InlineToolbarSeparator,
  InlineToolbarButton,
  InlineToolbarSelect,
  InlineToolbarGroup,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuTriggerButton
} from '@concretecms/backendui';

</script>
