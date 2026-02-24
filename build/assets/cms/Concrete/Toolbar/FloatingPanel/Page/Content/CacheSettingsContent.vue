<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  FloatingPanelContentForm,
  FloatingPanelContentFormActions,
  useAjax,
} from '@concretecms/backendui'

type CachePayload = {
  pageId: number | null
  global: {
    cacheEnabled: boolean
    mode: string
    modeLabel: string
    lifetimeMode: string
    lifetimeLabel: string
  }
  form: {
    cacheMode: string
    lifetimeMode: string
    customLifetimeMinutes: number | null
  }
  status: {
    state: 'cached' | 'unknown' | 'not_cached'
    message: string
    expiresAt?: string | null
    canPurge: boolean
  }
  actions: {
    submitUrl: string
    purgeUrl: string
  }
}

const props = withDefaults(defineProps<{
  loading?: boolean
  error?: string | null
  data?: CachePayload | null
}>(), {
  loading: false,
  error: null,
  data: null,
})
const emit = defineEmits<{
  (event: 'saved', payload: { title?: string, message?: string }): void
}>()
const { request } = useAjax()

const localCacheMode = ref('-1')
const localLifetimeMode = ref('0')
const localCustomMinutes = ref<number | null>(null)
const saving = ref(false)

watch(
  () => props.data,
  (data) => {
    if (!data) {
      return
    }
    localCacheMode.value = data.form.cacheMode
    localLifetimeMode.value = data.form.lifetimeMode
    localCustomMinutes.value = data.form.customLifetimeMinutes
  },
  { immediate: true },
)

const cacheEnabled = computed(() => {
  if (!props.data) {
    return false
  }
  if (localCacheMode.value === '1') {
    return true
  }
  if (localCacheMode.value === '-1') {
    return props.data.global.cacheEnabled
  }
  return false
})

const customLifetimeEnabled = computed(() => cacheEnabled.value && localLifetimeMode.value === 'custom')

function onSubmit() {
  if (!props.data || saving.value) {
    return
  }

  saving.value = true
  const data = {
    cCacheFullPageContent: localCacheMode.value,
    cCacheFullPageContentOverrideLifetime: localLifetimeMode.value,
    cCacheFullPageContentLifetimeCustom: localCustomMinutes.value ?? '',
  }
  request({
    url: props.data.actions.submitUrl,
    method: 'POST',
    expectJson: true,
    data: data,
    onSuccess: (response: any) => {
      emit('saved', {
        title: response?.title,
        message: response?.message,
      })
    },
    onComplete: () => {
      saving.value = false
    },
  })
}
</script>

<template>
  <div class="h-full min-h-[24rem] rounded-none bg-slate-50 text-slate-700">
    <div v-if="loading" class="p-6 text-sm text-slate-600">
      Loading caching settings...
    </div>

    <div v-else-if="error" class="m-6 rounded-lg bg-error/10 px-4 py-3 text-sm text-error">
      {{ error }}
    </div>

    <FloatingPanelContentForm
      v-else-if="data"
      class="h-full"
      @submit.prevent="onSubmit"
    >
      <div class="space-y-5">
        <div>
          <h4 class="text-sm font-semibold text-slate-800">Page Caching</h4>
          <p class="mt-1 text-xs text-slate-500">
            Configure full-page caching behavior for this page.
          </p>
        </div>

        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-4">
          <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Enable Cache</div>
          <label class="label cursor-pointer justify-start gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <input v-model="localCacheMode" type="radio" class="radio radio-sm" value="-1">
            <span class="label-text text-slate-700">Use global setting - {{ data.global.modeLabel }}</span>
          </label>
          <label class="label cursor-pointer justify-start gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <input v-model="localCacheMode" type="radio" class="radio radio-sm" value="0">
            <span class="label-text text-slate-700">Do not cache this page.</span>
          </label>
          <label class="label cursor-pointer justify-start gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <input v-model="localCacheMode" type="radio" class="radio radio-sm" value="1">
            <span class="label-text text-slate-700">Cache this page.</span>
          </label>
        </div>

        <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-4" :class="!cacheEnabled ? 'opacity-60' : ''">
          <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Duration</div>
          <label class="label cursor-pointer justify-start gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <input v-model="localLifetimeMode" type="radio" class="radio radio-sm" value="0" :disabled="!cacheEnabled">
            <span class="label-text text-slate-700">Use global setting - {{ data.global.lifetimeLabel }}</span>
          </label>
          <label class="label cursor-pointer justify-start gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <input v-model="localLifetimeMode" type="radio" class="radio radio-sm" value="forever" :disabled="!cacheEnabled">
            <span class="label-text text-slate-700">Until manually cleared</span>
          </label>
          <label class="label cursor-pointer justify-start gap-3 rounded-lg px-2 py-2 hover:bg-slate-50">
            <input v-model="localLifetimeMode" type="radio" class="radio radio-sm" value="custom" :disabled="!cacheEnabled">
            <span class="label-text text-slate-700">Custom duration</span>
          </label>
          <div class="flex items-center gap-2 ps-8">
            <input
              v-model.number="localCustomMinutes"
              type="number"
              class="input input-bordered input-sm w-28"
              min="1"
              :disabled="!customLifetimeEnabled"
            >
            <span class="text-xs text-slate-500">minutes</span>
          </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-4">
          <div class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Cache Status</div>
          <div
            class="mt-3 rounded-lg border px-3 py-2 text-sm"
            :class="
              data.status.state === 'cached'
                ? 'border-success/30 bg-success/10 text-success-content'
                : 'border-slate-200 bg-slate-50 text-slate-700'
            "
          >
            <div>{{ data.status.message }}</div>
            <div v-if="data.status.expiresAt" class="mt-1 text-xs opacity-80">Expires {{ data.status.expiresAt }}</div>
          </div>

          <div class="mt-3 flex items-center gap-2">
            <button type="button" class="btn btn-outline btn-sm" :disabled="!data.status.canPurge">Purge</button>
            <span class="text-xs text-slate-500">Stub action for now.</span>
          </div>

          <p class="mt-3 text-xs text-slate-500">
            Note: Site-wide caching can be configured from System &amp; Settings.
          </p>
        </div>
      </div>

      <template #actions>
        <FloatingPanelContentFormActions>
          <template #confirm>
            <button type="submit" class="btn btn-primary btn-sm" :disabled="saving">
              {{ saving ? 'Saving…' : 'Save Changes' }}
            </button>
          </template>
        </FloatingPanelContentFormActions>
      </template>
    </FloatingPanelContentForm>

    <div v-else class="p-6 text-sm text-slate-500">
      Select cache settings to begin.
    </div>
  </div>
</template>
