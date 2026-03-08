<template>
  <!-- In-page custom elements render without shadow DOM, so Tailwind utility classes are not available here. -->
  <!-- To edit the styles for classes used here, look for _containers.scss in the cms.scss file. // -->
  <div class="concrete-container">
    <slot />
  </div>
</template>

<script setup lang="ts">
</script>
