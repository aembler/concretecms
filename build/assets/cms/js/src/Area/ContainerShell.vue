<template>
  <div ref="rootEl" class="ccm-container-shell"></div>
  <Teleport v-if="slotTarget" :to="slotTarget">
    <slot />
  </Teleport>
</template>

<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from 'vue'

const props = defineProps<{
  container: {
    start?: string
    end?: string
  } | null
}>()

const rootEl = ref<HTMLElement | null>(null)
const slotTarget = ref<HTMLElement | null>(null)

async function renderShell() {
  await nextTick()

  if (!rootEl.value) {
    return
  }

  rootEl.value.innerHTML = ''
  slotTarget.value = null

  const start = props.container?.start ?? ''
  const end = props.container?.end ?? ''
  if (!start && !end) {
    slotTarget.value = rootEl.value
    return
  }

  const template = document.createElement('template')
  template.innerHTML = `${start}<div data-container-shell-slot="true"></div>${end}`
  rootEl.value.appendChild(template.content.cloneNode(true))
  slotTarget.value = rootEl.value.querySelector('[data-container-shell-slot="true"]') as HTMLElement | null
}

watch(
  () => props.container,
  () => {
    void renderShell()
  },
  { immediate: true, deep: true }
)

onMounted(() => {
  void renderShell()
})
</script>
