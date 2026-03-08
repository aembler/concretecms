<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import {
  FloatingPanel,
  FloatingPanelHeader,
  useAjax,
  normalizeJsonResponse,
} from '@concretecms/backendui'
import { ClockIcon } from '@heroicons/vue/24/outline'

type CheckInPanelData = {
  submitUrl: string
  requireVersionComments: boolean
  canApprovePageVersions: boolean
  publish: {
    buttonTitle: string
    enabled: boolean
    workflowLocked: boolean
    workflowLockedMessage: string
    errors: string[]
  }
  schedule: {
    publishDate: string | null
  }
  save: {
    label: string
  }
  discard: {
    available: boolean
    label: string
    confirm: string
  }
  labels: {
    title: string
    description: string
    comments: string
    schedule: string
    publishDate: string
    publishEndDate: string
    keepOtherSchedulingLabel: string
    keepOtherSchedulingHelpUnchecked: string
    keepOtherSchedulingHelpChecked: string
    publish: string
    scheduleAction: string
    cancel: string
  }
}

const props = withDefaults(defineProps<{
  pageId: Number,
  open?: boolean,
}>(), {
  open: false,
})

const emit = defineEmits<{
  (event: 'update:open', value: boolean): void
}>()

const { request } = useAjax()
const comments = ref('')
const scheduleModeActive = ref(false)
const scheduleDate = ref('')
const scheduleEndDate = ref('')
const keepOtherScheduling = ref(false)
const isSubmitting = ref(false)
const submissionErrors = ref<string[]>([])
const panelData = ref<CheckInPanelData | null>(null)
const hasLoaded = ref(false)
const isPanelReady = computed(() => panelData.value !== null)

const modelOpen = computed({
  get: () => props.open && isPanelReady.value,
  set: (value: boolean) => emit('update:open', value),
})

watch(
  () => panelData.value,
  (data) => {
    if (data) {
      comments.value = ''
      scheduleModeActive.value = false
      scheduleDate.value = data.schedule.publishDate
      scheduleEndDate.value = ''
      keepOtherScheduling.value = false
      submissionErrors.value = []
    }
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

function submit(action: 'save' | 'publish' | 'schedule' | 'discard') {
  if (!panelData.value) {
    return
  }

  submissionErrors.value = []
  isSubmitting.value = true

  const data = new FormData()
  data.set('comments', comments.value)
  data.set('action', action)
  data.set('approve', 'PREVIEW')

  if (action === 'schedule') {
    data.set('cvPublishDate', scheduleDate.value)
    if (scheduleEndDate.value) {
      data.set('cvPublishEndDate', scheduleEndDate.value)
    }
    data.set('keepOtherScheduling', keepOtherScheduling.value ? '1' : '0')
  }
  
  request({
    url: panelData.value.submitUrl,
    method: 'POST',
    data,
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
    onComplete: () => {
      isSubmitting.value = false
    },
  })
}

function loadPanelData() {
  if (hasLoaded.value) {
    return
  }
  hasLoaded.value = true
  const panelUrl = `/ccm/system/panels/page/check_in?cID=${props.pageId}`
  request({
    url: panelUrl,
    method: 'GET',
    onSuccess: (data: any) => {
      panelData.value = data ?? null
    },
  })
}

watch(() => props.open, (isOpen) => {
  if (isOpen) {
    loadPanelData()
  }
})
</script>

<template>
  <div class="fixed left-6 right-6 top-[5.25rem] z-[var(--index-layer-panel)]">
    <FloatingPanel
      v-model:open="modelOpen"
      width="min(92vw, 30rem)"
    >
      <template #header>
        <FloatingPanelHeader
          :title="panelData?.labels?.title"
          :description="panelData?.labels?.description"
          :closeable="true"
          :expandable="false"
        />
      </template>

      <template #default>
        <div class="space-y-3 px-4 py-3">
          <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ panelData.labels.comments }}</legend>
            <textarea
              v-model="comments"
              class="textarea textarea-bordered w-full min-h-24"
              :required="Boolean(panelData?.requireVersionComments)"
            />
          </fieldset>

          <div class="mb-4">
            <div class="join w-full" v-if="panelData.publish.enabled">
              <button
                  type="button"
                  class="btn btn-primary join-item flex-1"
                  :disabled="isSubmitting || scheduleModeActive"
                  @click="submit('publish')"
              >
                {{ panelData?.publish.buttonTitle }}
              </button>
              <button
                  type="button"
                  class="btn btn-primary join-item"
                  :disabled="isSubmitting"
                  @click="scheduleModeActive = !scheduleModeActive"
              >
                <ClockIcon class="w-4 h-4" />
              </button>
            </div>
          </div>


          <div v-if="panelData?.canApprovePageVersions">

            <div v-if="scheduleModeActive" class="rounded-box border border-base-300 bg-base-100 p-3 text-sm">
              <div class="flex flex-col gap-3">
                <label class="form-control w-full">
                  <span class="label-text mb-1">{{ panelData?.labels?.publishDate }}</span>
                  <input v-model="scheduleDate" type="datetime-local" class="input input-bordered w-full" />
                </label>
                <label class="form-control w-full">
                  <span class="label-text mb-1">{{ panelData?.labels?.publishEndDate }}</span>
                  <input v-model="scheduleEndDate" type="datetime-local" class="input input-bordered w-full" />
                </label>
                <label class="label cursor-pointer justify-start gap-3">
                  <input v-model="keepOtherScheduling" type="checkbox" class="toggle toggle-primary" />
                  <span class="label-text">{{ panelData?.labels?.keepOtherSchedulingLabel }}</span>
                </label>
                <div class="alert alert-info text-sm">
                  {{
                    keepOtherScheduling
                      ? panelData?.labels?.keepOtherSchedulingHelpChecked
                      : panelData?.labels?.keepOtherSchedulingHelpUnchecked
                  }}
                </div>
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    :disabled="!panelData?.publish?.enabled || !scheduleDate || isSubmitting"
                    @click="submit('schedule')"
                >
                  {{ panelData?.labels?.scheduleAction }}
                </button>
              </div>
            </div>

            <div v-if="panelData.publish?.workflowLocked" class="alert alert-info text-sm">
              {{ panelData.publish.workflowLockedMessage }}
            </div>

          </div>

          <div class="divider my-2" />

          <div class="flex flex-wrap gap-2">
            <button type="button" class="btn btn-success" :disabled="isSubmitting" @click="submit('save')">
              {{ panelData?.save?.label }}
            </button>
            <button
              v-if="panelData?.discard?.available"
              type="button"
              class="btn btn-error"
              :disabled="isSubmitting"
              @click="submit('discard')"
            >
              {{ panelData?.discard?.label }}
            </button>
            <button type="button" class="btn btn-ghost ms-auto" :disabled="isSubmitting" @click="modelOpen = false">
              {{ panelData?.labels?.cancel }}
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
