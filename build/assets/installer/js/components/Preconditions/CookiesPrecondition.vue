<template>
  <tr>
    <td>
      <CheckIcon v-if="cookiesEnabled" class="w-4 h-4 text-green-500" />
      <ExclamationCircleIcon v-else class="w-4 h-4 text-red-500" />
    </td>
    <td class="w-full">
            <span :class="{ 'text-red-500': !cookiesEnabled }">
                {{ precondition.precondition.name }}
            </span>
    </td>
    <td>
      <div
          v-if="!cookiesEnabled"
          class="tooltip"
          :data-tip="precondition.message_failed"
      >
        <QuestionMarkCircleIcon class="w-4 h-4 text-gray-400 cursor-pointer" />
      </div>
    </td>
  </tr>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import {
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
const cookiesEnabled = ref(null)

onMounted(() => {
  if (testCookies()) {
    cookiesEnabled.value = true
  } else {
    cookiesEnabled.value = false
    emit('precondition-failed', props.precondition)
  }
})

function testCookies() {
  if (typeof navigator.cookieEnabled === 'boolean') {
    return navigator.cookieEnabled
  }
  const COOKIE_NAME = 'CONCRETECMS_INSTALL_TEST'
  const COOKIE_VALUE = 'ok_' + Math.random()
  document.cookie = COOKIE_NAME + '=' + COOKIE_VALUE
  const result = document.cookie.indexOf(COOKIE_NAME + '=' + COOKIE_VALUE) >= 0
  document.cookie = COOKIE_NAME + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT'
  return result
}
</script>
