<template>
  <form>
    <div class="text-center">
      <img :src="logo" class="rounded-full bg-primary mx-auto mb-4" height="48" width="48" />
    </div>

    <h2 class="text-center mb-4 mt-4 text-4xl font-extrabold leading-none tracking-tight">
      {{ lang.stepContent }}
    </h2>

    <h3 class="text-center mb-4 mt-3 text-2xl font-semibold">
      Choose a Theme
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-center">
      <div v-for="startingPoint in featuredStartingPoints" :key="startingPoint.handle">
        <div class="card bg-base-100 shadow-md">
          <figure>
            <img :src="startingPoint.thumbnail" width="600" height="300" class="w-full"/>
          </figure>
          <div class="card-body items-center text-center">
            <h2 class="card-title">{{ startingPoint.name }}</h2>
            <ul class="text-sm mt-3 mb-4 space-y-1">
              <li v-for="(line, i) in startingPoint.description" :key="i">
                {{ line }}
              </li>
            </ul>
            <button
                type="button"
                class="btn btn-outline btn-primary w-full"
                :class="{ 'btn-active': selectedStartingPoint === startingPoint.handle }"
                @click="selectedStartingPoint = startingPoint.handle"
            >
                            <span v-if="selectedStartingPoint === startingPoint.handle">
                                {{ lang.selected }}
                                <CheckIcon class="inline w-4 h-4 ml-1"/>
                            </span>
              <span v-else>
                                {{ lang.select }} {{ startingPoint.name }}
                            </span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card bg-base-100 shadow-md mb-6">
      <div class="card-title px-6 pt-6">{{ lang.otherStartingPoints }}</div>
      <div class="card-body space-y-4">
        <div
            v-for="(startingPoint, i) in otherStartingPoints"
            :key="startingPoint.handle"
            class="flex items-center"
            :class="{ 'mb-4': i + 1 < otherStartingPoints.length }"
        >
          <div class="mr-4">
            <div class="font-semibold">{{ startingPoint.name }}</div>
            <div class="text-gray-500 text-sm">{{ startingPoint.description }}</div>
          </div>
          <button
              type="button"
              class="btn btn-outline btn-primary ml-auto"
              :class="{ 'btn-active': selectedStartingPoint === startingPoint.handle }"
              @click="selectedStartingPoint = startingPoint.handle"
          >
            <span v-if="selectedStartingPoint === startingPoint.handle">
                {{ lang.selected }}
                <CheckIcon class="inline w-4 h-4 ml-1"/>
            </span>
                    <span v-else>
                {{ lang.select }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <Actions>
      <button
          type="button"
          class="me-auto btn btn-secondary"
          @click="$emit('previous')"
      >
        {{ lang.back }}
      </button>

      <button
          type="button"
          class="ms-auto btn btn-primary"
          :disabled="!selectedStartingPoint"
          @click="$emit('select-starting-point', selectedStartingPoint)"
      >
        {{ lang.next }}
      </button>
    </Actions>
  </form>
</template>

<script setup>
import {ref, computed, onMounted} from 'vue'
import {CheckIcon} from '@heroicons/vue/24/outline'
import Actions from "./Actions.vue";

const props = defineProps({
  logo: {type: String, required: true},
  startingPoint: {type: String, required: false},
  startingPoints: {type: Array, required: true},
  lang: {type: Object, required: true}
})

const emit = defineEmits(['previous', 'select-starting-point'])
const selectedStartingPoint = ref('')

const featuredStartingPoints = computed(() =>
    props.startingPoints.filter(sp => sp.thumbnail)
)

const otherStartingPoints = computed(() =>
    props.startingPoints.filter(sp => !sp.thumbnail)
)

onMounted(() => {
  selectedStartingPoint.value = props.startingPoint
})
</script>
