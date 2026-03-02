<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  FloatingPanel,
  FloatingPanelHeader,
  useAjax,
  normalizeJsonResponse,
} from '@concretecms/backendui'

type CheckInPanelData = {
  submitUrl: string
  requireVersionComments?: boolean
  canApprovePageVersions?: boolean
  publish?: {
    title?: string
    enabled?: boolean
    workflowLocked?: boolean
    workflowLockedMessage?: string
    errors?: string[]
  }
  schedule?: {
    publishDate?: string | null
  }
  save?: {
    label?: string
  }
  discard?: {
    available?: boolean
    label?: string
    confirm?: string
  }
  labels?: {
    title?: string
    description?: string
    comments?: string
    schedule?: string
    publishDate?: string
    publishEndDate?: string
    keepOtherScheduling?: string
    publish?: string
    scheduleAction?: string
    cancel?: string
  }
}

const props = withDefaults(defineProps<{
  open?: boolean
  loading?: boolean
  error?: string | null
  data?: CheckInPanelData | null
}>(), {
  open: false,
  loading: false,
  error: null,
  data: null,
})

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
}>()

const { request } = useAjax()
const isExpanded = ref(false)
const comments = ref('')
const scheduleOpen = ref(false)
const scheduleDate = ref('')
const scheduleEndDate = ref('')
const keepOtherScheduling = ref(false)
const isSubmitting = ref(false)
const submissionErrors = ref<string[]>([])

const modelOpen = computed({
  get: () => props.open,
  set: (value: boolean) => emit('update:open', value),
})

const labels = computed(() => props.data?.labels ?? {})
const panelTitle = computed(() => labels.value.title || 'Check In')
const panelDescription = computed(() => labels.value.description || 'Save, publish, schedule, or discard your page edits.')
const commentsLabel = computed(() => labels.value.comments || 'Version Comments')
const scheduleLabel = computed(() => labels.value.schedule || 'Schedule Publish')
const publishDateLabel = computed(() => labels.value.publishDate || 'Publish Date')
const publishEndDateLabel = computed(() => labels.value.publishEndDate || 'Publish End Date')
const keepOtherSchedulingLabel = computed(() => labels.value.keepOtherScheduling || 'Keep Other Scheduling')
const publishLabel = computed(() => props.data?.publish?.title || labels.value.publish || 'Publish')
const scheduleActionLabel = computed(() => labels.value.scheduleAction || 'Schedule')
const cancelLabel = computed(() => labels.value.cancel || 'Cancel')
const saveLabel = computed(() => props.data?.save?.label || 'Save Changes')
const discardLabel = computed(() => props.data?.discard?.label || 'Discard Changes')

const publishErrors = computed(() => props.data?.publish?.errors ?? [])
const canPublish = computed(() => Boolean(props.data?.canApprovePageVersions))
const publishEnabled = computed(() => Boolean(props.data?.publish?.enabled))
const discardAvailable = computed(() => Boolean(props.data?.discard?.available))

watch(
  () => props.data,
  (data) => {
    comments.value = ''
    scheduleOpen.value = Boolean(data?.schedule?.publishDate)
    scheduleDate.value = toDateTimeLocal(data?.schedule?.publishDate)
    scheduleEndDate.value = ''
    keepOtherScheduling.value = false
    submissionErrors.value = []
  },
  { immediate: true }
)

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      submissionErrors.value = []
    }
  }
)

function toDateTimeLocal(value: string | null | undefined): string {
  if (!value) {
    return ''
  }
  const normalized = String(value).trim().replace(' ', 'T')
  return normalized.length >= 16 ? normalized.slice(0, 16) : normalized
}

function toSqlDateTime(value: string): string {
  if (!value) {
    return ''
  }
  const normalized = String(value).trim().replace('T', ' ')
  return normalized.length === 16 ? `${normalized}:00` : normalized
}

function submit(action: 'save' | 'publish' | 'schedule' | 'discard') {
  if (isSubmitting.value || !props.data?.submitUrl) {
    return
  }

  if (action === 'discard' && props.data?.discard?.confirm && !window.confirm(props.data.discard.confirm)) {
    return
  }

  submissionErrors.value = []
  isSubmitting.value = true

  const body = new URLSearchParams()
  body.set('comments', comments.value)
  body.set('action', action)
  body.set('approve', 'PREVIEW')

  if (action === 'schedule') {
    body.set('cvPublishDate', toSqlDateTime(scheduleDate.value))
    if (scheduleEndDate.value) {
      body.set('cvPublishEndDate', toSqlDateTime(scheduleEndDate.value))
    }
    body.set('keepOtherScheduling', keepOtherScheduling.value ? '1' : '0')
  }

  request({
    url: props.data.submitUrl,
    method: 'POST',
    body,
    skipResponseValidation: true,
    onSuccess: (response: any) => {
      const normalized: any = normalizeJsonResponse(response)
      if (normalized?.error) {
        submissionErrors.value = Array.isArray(normalized?.errors) ? normalized.errors : ['Unable to complete check in.']
        return
      }

      const redirectUrl = normalized?.redirectURL
      if (redirectUrl) {
        window.location.href = redirectUrl
        return
      }

      modelOpen.value = false
    },
    onError: () => {
      submissionErrors.value = ['Unable to complete check in.']
    },
    onComplete: () => {
      isSubmitting.value = false
    },
  })
}
</script>

<template>
  <div class="fixed left-6 right-6 top-[5.25rem] z-[var(--index-layer-panel)]">
    <FloatingPanel
      v-model:open="modelOpen"
      v-model:expanded="isExpanded"
      width="min(92vw, 30rem)"
    >
      <template #header>
        <FloatingPanelHeader
          :title="panelTitle"
          :description="panelDescription"
          :closeable="true"
          :expandable="false"
        />
      </template>

      <template #default>
        <div v-if="loading" class="px-4 py-4 text-sm text-slate-600">Loading check in options...</div>
        <div v-else-if="error" class="alert alert-error mx-4 my-4 text-sm">{{ error }}</div>
        <div v-else-if="data" class="space-y-4 px-4 py-4">
          <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ commentsLabel }}</legend>
            <textarea
              v-model="comments"
              class="textarea textarea-bordered w-full min-h-24"
              :required="Boolean(data.requireVersionComments)"
            />
          </fieldset>

          <div v-if="canPublish" class="space-y-2">
            <div class="join w-full">
              <button
                type="button"
                class="btn btn-primary join-item flex-1"
                :disabled="!publishEnabled || isSubmitting"
                @click="submit('publish')"
              >
                {{ publishLabel }}
              </button>
              <button
                type="button"
                class="btn btn-primary join-item"
                :disabled="!publishEnabled || isSubmitting"
                @click="scheduleOpen = !scheduleOpen"
              >
                {{ scheduleActionLabel }}
              </button>
            </div>

            <div v-if="data.publish?.workflowLocked" class="alert alert-info text-sm">
              {{ data.publish.workflowLockedMessage }}
            </div>

            <div v-if="publishErrors.length" class="space-y-2">
              <div
                v-for="(errorMessage, index) in publishErrors"
                :key="`publish-error-${index}`"
                class="alert alert-warning text-sm"
              >
                {{ errorMessage }}
              </div>
            </div>

            <div v-if="scheduleOpen" class="rounded-box border border-base-300 bg-base-100 p-3 space-y-3">
              <p class="text-sm font-semibold">{{ scheduleLabel }}</p>
              <label class="form-control w-full">
                <span class="label-text mb-1">{{ publishDateLabel }}</span>
                <input v-model="scheduleDate" type="datetime-local" class="input input-bordered w-full" />
              </label>
              <label class="form-control w-full">
                <span class="label-text mb-1">{{ publishEndDateLabel }}</span>
                <input v-model="scheduleEndDate" type="datetime-local" class="input input-bordered w-full" />
              </label>
              <label class="label cursor-pointer justify-start gap-2">
                <input v-model="keepOtherScheduling" type="checkbox" class="checkbox checkbox-sm" />
                <span class="label-text">{{ keepOtherSchedulingLabel }}</span>
              </label>
              <button
                type="button"
                class="btn btn-primary btn-sm"
                :disabled="!publishEnabled || !scheduleDate || isSubmitting"
                @click="submit('schedule')"
              >
                {{ scheduleActionLabel }}
              </button>
            </div>
          </div>

          <div class="divider my-2" />

          <div class="flex flex-wrap gap-2">
            <button type="button" class="btn btn-success" :disabled="isSubmitting" @click="submit('save')">
              {{ saveLabel }}
            </button>
            <button
              v-if="discardAvailable"
              type="button"
              class="btn btn-error"
              :disabled="isSubmitting"
              @click="submit('discard')"
            >
              {{ discardLabel }}
            </button>
            <button type="button" class="btn btn-ghost ms-auto" :disabled="isSubmitting" @click="modelOpen = false">
              {{ cancelLabel }}
            </button>
          </div>

          <div v-if="submissionErrors.length" class="space-y-2">
            <div
              v-for="(errorMessage, index) in submissionErrors"
              :key="`submission-error-${index}`"
              class="alert alert-error text-sm"
            >
              {{ errorMessage }}
            </div>
          </div>
        </div>
      </template>
    </FloatingPanel>
  </div>
</template>
