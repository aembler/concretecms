<template>
  <div class="space-y-8">
    <!-- Logo -->
    <div class="text-center">
      <img :src="logo" class="bg-primary rounded-full max-h-12 mx-auto" />
    </div>

    <!-- Heading -->
    <h3 class="text-center text-xl font-semibold">{{ lang.stepConfirm }}</h3>

    <!-- SITE -->
    <div class="card bg-base-100 shadow">
      <div class="card-body space-y-4">
        <div class="card-title">{{ lang.confirm.site }}</div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.site }}</div>
          <div class="md:col-span-8">{{ installOptions.site.name }}</div>
        </div>
        <div v-if="installOptions.site.hasCanonicalUrl" class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.mainCanonicalUrl }}</div>
          <div class="md:col-span-8">{{ installOptions.site.canonicalUrl }}</div>
        </div>
        <div v-if="installOptions.site.hasAlternativeCanonicalUrl" class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.alternativeCanonicalUrl }}</div>
          <div class="md:col-span-8">{{ installOptions.site.alternativeCanonicalUrl }}</div>
        </div>
      </div>
    </div>

    <!-- ADMIN -->
    <div class="card bg-base-100 shadow">
      <div class="card-body space-y-4">
        <div class="card-title">{{ lang.confirm.adminUser }}</div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.email }}</div>
          <div class="md:col-span-8">{{ installOptions.adminUser.email }}</div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.password }}</div>
          <div class="md:col-span-8">
            <template v-if="showPassword">
              {{ installOptions.adminUser.password }}
            </template>
            <template v-else>
              <code>•••••••••••••</code>
              <a href="#" @click.prevent="showPassword = true" class="ml-2 text-primary underline text-sm">
                <EyeIcon class="inline w-4 h-4" />
              </a>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- DATABASE -->
    <div class="card bg-base-100 shadow">
      <div class="card-body space-y-4">
        <div class="card-title">{{ lang.confirm.database }}</div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.dbServer }}</div>
          <div class="md:col-span-8">{{ installOptions.database.server }}</div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.dbUsername }}</div>
          <div class="md:col-span-8">{{ installOptions.database.username }}</div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.dbPassword }}</div>
          <div class="md:col-span-8">
            <template v-if="showDatabasePassword">
              {{ installOptions.database.password }}
            </template>
            <template v-else>
              <code>•••••••••••••</code>
              <a href="#" @click.prevent="showDatabasePassword = true" class="ml-2 text-primary underline text-sm">
                <EyeIcon class="inline w-4 h-4" />
              </a>
            </template>
          </div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.dbDatabase }}</div>
          <div class="md:col-span-8">{{ installOptions.database.database }}</div>
        </div>
      </div>
    </div>

    <!-- SESSION -->
    <div v-if="installOptions.session.handler !== ''" class="card bg-base-100 shadow">
      <div class="card-body space-y-4">
        <div class="card-title">{{ lang.confirm.session }}</div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.sessionHandler }}</div>
          <div class="md:col-span-8">{{ installOptions.session.handler }}</div>
        </div>
      </div>
    </div>

    <!-- LOCALIZATION -->
    <div class="card bg-base-100 shadow">
      <div class="card-body space-y-4">
        <div class="card-title">{{ lang.confirm.localization }}</div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.locale }}</div>
          <div class="md:col-span-8"><code>{{ installOptions.locale }}</code></div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.language }}</div>
          <div class="md:col-span-8">{{ installOptions.localization.siteLocaleLanguage }}</div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.country }}</div>
          <div class="md:col-span-8">{{ installOptions.localization.siteLocaleCountry }}</div>
        </div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.timezone }}</div>
          <div class="md:col-span-8">{{ installOptions.localization.timezone }}</div>
        </div>
      </div>
    </div>

    <!-- CONTENT -->
    <div class="card bg-base-100 shadow">
      <div class="card-body space-y-4">
        <div class="card-title">{{ lang.confirm.content }}</div>
        <div class="grid md:grid-cols-12 gap-4 items-start">
          <div class="md:col-span-4 text-end font-semibold">{{ lang.startingPoint }}</div>
          <div class="md:col-span-8">{{ selectedStartingPointName }}</div>
        </div>
      </div>
    </div>

    <!-- ACTIONS -->
    <Actions>
      <button class="mr-auto btn btn-secondary" @click="$emit('previous')">
        {{ lang.back }}
      </button>
      <button class="ml-auto btn btn-primary" @click="$emit('next')">
        {{ lang.confirm.beginInstallation }}
      </button>
    </Actions>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { EyeIcon } from 'lucide-vue-next'
import Actions from "./Actions.vue";

const props = defineProps({
  logo: String,
  startingPoints: Array,
  lang: Object,
  installOptions: Object
})

const showPassword = ref(false)
const showDatabasePassword = ref(false)

const selectedStartingPointName = computed(() => {
  return props.startingPoints.find(sp => sp.handle === props.installOptions.startingPoint)?.name || ''
})
</script>
