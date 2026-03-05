<template>
  <div>
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
</template>
