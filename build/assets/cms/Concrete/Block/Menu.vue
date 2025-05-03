<template>
  <InlineToolbar class="flex-nowrap">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <DropdownMenuTriggerButton>
          {{ selectedVariantName }}
        </DropdownMenuTriggerButton>
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <DropdownMenuItem>
          Default
        </DropdownMenuItem>
        <DropdownMenuItem v-for="variant in variants">
          {{  variant.name }}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
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
          <EllipsisVerticalIcon class="size-4 cursor-pointer hover:text-primary transition" />
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

<script setup lang="ts">
import { computed } from "vue"
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

const props = defineProps({
  variants: Array<{ file: String; name: String }>,
  selectedVariant: String
})

const selectedVariantName = computed(() => {
  let selectedVariantName
  if (props.variants) {
    props.variants.forEach((variant) => {
      if (variant.file === props.selectedVariant) {
        selectedVariantName = variant.name
      }
    })
  }

  if (!selectedVariantName) {
    selectedVariantName = 'Default'
  }

  return selectedVariantName
})



</script>
