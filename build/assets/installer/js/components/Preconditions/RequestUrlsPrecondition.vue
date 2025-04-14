<template>
  <tr>
    <td>
      <ArrowPathIcon v-if="requestUrlsSuccess === null" class="w-4 h-4 animate-spin text-gray-400" />
      <CheckIcon v-else-if="requestUrlsSuccess" class="w-4 h-4 text-green-500" />
      <ExclamationCircleIcon v-else class="w-4 h-4 text-red-500" />
    </td>
    <td class="w-full">
            <span :class="{ 'text-red-500': requestUrlsSuccess === false || ajaxFailed }">
                {{ precondition.precondition.name }}
            </span>
    </td>
    <td>
      <div
          v-if="requestUrlsSuccess === false || ajaxFailed"
          class="tooltip"
          :data-tip="failureMessage"
      >
        <QuestionMarkCircleIcon class="w-4 h-4 text-gray-400 cursor-pointer" />
      </div>
    </td>
  </tr>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import {
  ArrowPathIcon,
  CheckIcon,
  ExclamationCircleIcon,
  QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  precondition: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['precondition-failed'])
const requestUrlsSuccess = ref(null)
const ajaxFailed = ref(false)

const failureMessage = computed(() => {
  if (ajaxFailed.value) {
    return props.precondition.precondition.ajax_fail_message
  } else if (!requestUrlsSuccess.value) {
    return props.precondition.precondition.error_message
  }
})

watch(requestUrlsSuccess, (value) => {
  if (value === false) {
    emit('precondition-failed', props.precondition)
  }
})

onMounted(() => {
  $.ajax({
    cache: false,
    dataType: 'json',
    method: 'GET',
    url: props.precondition.precondition.ajax_url
  })
      .done((data) => {
        if (data.response === 400) {
          requestUrlsSuccess.value = true
        } else {
          requestUrlsSuccess.value = false
        }
      })
      .fail(() => {
        requestUrlsSuccess.value = false
        ajaxFailed.value = true
      })
})
</script>
