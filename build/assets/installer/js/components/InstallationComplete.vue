<template>
  <div class="flex flex-col items-center px-4 py-8">
    <img :src="logo" class="rounded-full bg-primary mx-auto mb-4" height="48" width="48" />

    <h3 class="text-center text-2xl font-semibold mt-6 mb-4">
      {{ lang.stepInstallationComplete }}
    </h3>

    <div class="max-w-xl w-full text-center">
      <div class="mb-6 text-left" v-html="lang.installationCompleteMessage"></div>

      <a
          :href="installationCompleteUrl"
          class="btn btn-primary btn-lg"
      >
        {{ lang.editYourSite }}
      </a>
    </div>
  </div>
</template>

<script setup>
defineProps({
  installationCompleteUrl: {
    type: String,
    required: true,
  },
  logo: {
    type: String,
    required: true,
  },
  lang: {
    type: Object,
    required: true,
  },
});
</script>
