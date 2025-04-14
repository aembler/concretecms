<template>
  <tr>
    <td>
      <CheckIcon v-if="precondition.result.state === 1" class="w-4 h-4 text-green-500" />
      <ExclamationTriangleIcon v-else-if="precondition.result.state === 2" class="w-4 h-4 text-yellow-500" />
      <ExclamationCircleIcon v-else-if="precondition.result.state === 4" class="w-4 h-4 text-red-500" />
    </td>
    <td class="w-full">
            <span :class="{ 'text-red-500': precondition.result.state === 4 }">
                {{ precondition.precondition.name }}
            </span>
    </td>
    <td>
      <div
          v-if="precondition.result.message"
          class="tooltip"
          :data-tip="precondition.result.message"
      >
        <QuestionMarkCircleIcon class="w-4 h-4 text-gray-400 cursor-pointer" />
      </div>
    </td>
  </tr>
</template>

<script setup>
import { onMounted } from 'vue'
import {
  CheckIcon,
  ExclamationTriangleIcon,
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

onMounted(() => {
  if (props.precondition.result.state !== 1) {
    emit('precondition-failed', props.precondition)
  }
})
</script>
