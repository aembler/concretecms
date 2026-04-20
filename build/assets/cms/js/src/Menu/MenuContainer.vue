<template>
  <Teleport :to="uiStore.menuContainer">
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-300"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <slot />
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { useUiStore } from '@concretecms/backendui'
const uiStore = useUiStore();
</script>
