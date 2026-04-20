<template>
  <MenuContainer>
    <div
        :id="id"
        v-show="show"
        class="z-(--index-layer-page-menu) flex absolute pointer-events-auto -translate-x-1/2"
        ref="menuEl"
        :style="{ top: menuTop, left: menuLeft }"
    >
      <InlineToolbar class="flex-nowrap">
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <!-- Keep trigger events local to Radix; the global block click observer can
                 otherwise intercept same-trigger clicks and cause close-then-reopen. -->
            <button type="button" class="inline-flex items-center gap-2 btn bg-base-100 btn-secondary btn-sm" @pointerdown.stop @click.stop>
              <label class="text-nowrap">{{ selectedVariantName }}</label>
              <ChevronDownIcon class="size-4" />
            </button>
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
          <InlineToolbarButton @click="$emit('edit')">
            <PencilIcon class="size-4" />
          </InlineToolbarButton>

          <InlineToolbarButton>
            <ClipboardIcon class="size-4" />
          </InlineToolbarButton>

          <InlineToolbarButton @click="$emit('delete')">
            <TrashIcon class="size-4 text-error" />
          </InlineToolbarButton>

          <InlineToolbarSeparator />

          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <!-- Keep trigger events local to Radix; the global block click observer can
                   otherwise intercept same-trigger clicks and cause close-then-reopen. -->
              <button type="button" class="inline-flex items-center justify-center btn btn-sm bg-base-100 btn-secondary px-2" @pointerdown.stop @click.stop>
                <EllipsisVerticalIcon class="size-4 cursor-pointer hover:text-primary transition" />
              </button>
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
    </div>
  </MenuContainer>
</template>

<script setup lang="ts">
import { ref, computed, toRef } from "vue"
import { PencilIcon, ClipboardIcon, TrashIcon, EllipsisVerticalIcon } from '@heroicons/vue/24/outline'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'
import {
  InlineToolbar,
  InlineToolbarSeparator,
  InlineToolbarButton,
  InlineToolbarGroup,
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@concretecms/backendui';
import MenuContainer from "../Menu/MenuContainer.vue";
import { useMenuPositioner } from "../Menu/positioner";

const emit = defineEmits(['edit', 'delete'])

const props = defineProps({
  blockElement: HTMLElement,
  variants: Array<{ file: String; name: String }>,
  blocktype: Object,
  selectedVariant: String,
  show: Boolean,
  id: String
})

const menuEl = ref<HTMLElement | null>(null)
const blockElementRef = toRef(props, 'blockElement')
const showRef = toRef(props, 'show')
const menuPos = useMenuPositioner(blockElementRef, menuEl, showRef)
const menuLeft = computed(() => `${menuPos.x.value}px`)
const menuTop = computed(() => `${menuPos.y.value}px`)

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
