<template>
  <div>
    <!-- Spinner over logo -->
    <div class="relative text-center">
      <div class="absolute inset-x-0 -top-2 flex justify-center">
        <div class="w-16 h-16 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
      </div>
      <img
          :src="logo"
          class="bg-primary rounded-full mx-auto max-h-12 mt-4"
      />
    </div>

    <!-- Heading -->
    <div>
      <h3 class="text-center mt-4 mb-6 text-xl font-semibold">{{ lang.stepPerformInstallation }}</h3>
    </div>

    <!-- Interstitial content -->
    <div id="interstitial-message">
      <div v-if="installError || currentProgress" class="mb-4">
        <div v-if="installError" class="alert alert-error shadow">
          <span v-html="installError" />
        </div>
        <div v-else class="text-center text-lg">
          {{ currentProgress }}
        </div>
      </div>

      <!-- Info Card -->
      <div class="card bg-base-100 shadow">
        <div class="card-title p-4 border-b border-base-300">
          {{ lang.interstitial.whileYouWait }}
        </div>
        <div class="card-body space-y-4">
          <div>
            <h4 class="font-semibold">{{ lang.interstitial.forums }}</h4>
            <p v-html="lang.interstitial.forumsMessage"></p>
          </div>
          <div>
            <h4 class="font-semibold">{{ lang.interstitial.userDocumentation }}</h4>
            <p v-html="lang.interstitial.userDocumentationMessage"></p>
          </div>
          <div>
            <h4 class="font-semibold">{{ lang.interstitial.screencasts }}</h4>
            <p v-html="lang.interstitial.screencastsMessage"></p>
          </div>
          <div>
            <h4 class="font-semibold">{{ lang.interstitial.developerDocumentation }}</h4>
            <p v-html="lang.interstitial.developerDocumentationMessage"></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useAjax } from '@concretecms/backendui'
import NProgress from 'nprogress'
import { toFormData } from '../formData'

const props = defineProps({
  logo: { type: String, required: true },
  startingPointRoutineUrl: { type: String, required: true },
  beginInstallationUrl: { type: String, required: true },
  installOptions: { type: Object, required: true },
  lang: { type: Object, required: true }
})

const routines = ref(null)
const currentRoutine = ref(null)
const currentProgress = ref(null)
const installError = ref(null)

const emit = defineEmits(['installation-complete'])
const { request } = useAjax()

function initProgressBar() {
  NProgress.configure({ showSpinner: false })
}

watch(currentRoutine, async (routineIndex) => {
  if (!routines.value || routineIndex === null) return

  if (routines.value.length > routineIndex) {
    const routine = routines.value[routineIndex]
    const url = `${props.startingPointRoutineUrl}/${props.installOptions.startingPoint}`
    currentProgress.value = routine.text

    await request({
      url,
      method: 'POST',
      data: toFormData({
        routine,
        options: props.installOptions,
      }),
      skipResponseValidation: true,
      onSuccess: (response) => {
        if (response.error) {
          installError.value = response.message
        } else {
          currentRoutine.value++
          console.log(currentRoutine.value, routines.value.length)
          NProgress.set(currentRoutine.value / routines.value.length)
        }
      },
      onError: (error) => {
        throw error
      },
    })
  } else {
    NProgress.done()
    currentProgress.value = props.lang.installationComplete
    emit('installation-complete')
  }
})

onMounted(async () => {
  initProgressBar()
  currentProgress.value = props.lang.loadingInstallationRoutines

  await request({
    url: props.beginInstallationUrl,
    method: 'POST',
    data: toFormData(props.installOptions),
    skipResponseValidation: true,
    onSuccess: (response) => {
      routines.value = response
      currentRoutine.value = 0
    },
    onError: (error) => {
      throw error
    },
  })
})
</script>
