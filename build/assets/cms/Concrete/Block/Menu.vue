<template>
  <InlineToolbar>
    <InlineToolbarSeparator/>
    <InlineToolbarText>Columns</InlineToolbarText>
    <InlineToolbarInput type="number" default-value="3" class="w-[60px]"></InlineToolbarInput>
    <InlineToolbarSeparator/>
    <InlineToolbarButtonGroup>
      <InlineToolbarButtonButton>Cancel</InlineToolbarButtonButton>
      <InlineToolbarButtonButton variant="primary">Add Layout</InlineToolbarButtonButton>
    </InlineToolbarButtonGroup>
  </InlineToolbar>
</template>

<script setup>
import {
  InlineToolbar,
  InlineToolbarSeparator,
  InlineToolbarButton,
  InlineToolbarButtonButton,
  InlineToolbarText,
  InlineToolbarInput,
  InlineToolbarButtonGroup
} from '@concretecms/backendui';

</script>
