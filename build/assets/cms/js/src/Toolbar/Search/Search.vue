<template>
  <div class="relative w-60 max-w-sm">
    <button
        class="absolute left-2 top-1/2 -translate-y-1/2 z-250"
        @click="emitSearch"
        aria-label="Search"
    >
      <MagnifyingGlassIcon class="w-5 h-5 text-gray-500" />
    </button>
    <input
        v-model="query"
        type="text"
        placeholder="Search..."
        class="input border-transparent pl-8 c-shadow focus:shadow-[1px_1px_10px_rgba(183,183,183,0.3)]"
        @keydown.enter="emitSearch"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'

const query = ref('')
const emit = defineEmits(['search'])

const emitSearch = () => {
  emit('search', query.value.trim())
}
</script>
