<script setup lang="ts">
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'
import { useAjax } from '@concretecms/backendui'
import { useUiStore } from '@concretecms/backendui'
const ui = useUiStore()

const props = defineProps<{
  helpUrl: string
}>()

const modal = ref<HTMLDialogElement | null>(null)
const sidebarContent = ref<string>()

function showModal(event: MouseEvent) {
  event.preventDefault()
  const { request } = useAjax()

  request({
    url: props.helpUrl,
    method: 'GET',
    skipResponseValidation: true,
    onSuccess: (data: { sidebar: string }) => {
      console.log(data)
      sidebarContent.value = data.sidebar
    },
    onComplete: () => {
      modal.value?.showModal()
    },
  })
}
</script>

<template>
  <div
      :class="[ui.toolbar.showTooltips && 'tooltip tooltip-bottom']"
      :data-tip="ui.toolbar.showTooltips ? 'Help' : null"
  >
    <a
        :href="helpUrl"
        @click="showModal"
        class="c-toolbar-button"
        title="Help"
    >
      <QuestionMarkCircleIcon class="w-4 h-4" />
      <span v-if="ui.toolbar.showTitles">Help</span>
    </a>
  </div>

  <dialog ref="modal" class="modal">
    <div class="modal-box max-w-3xl">
      <form method="dialog">
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
      </form>
      <h3 class="text-lg font-bold pb-3">Help</h3>
      <!-- @TODO: make announcement items show up in here like in v9 //-->
      <div class="flex flex-col gap-2" v-html="sidebarContent" />
    </div>
  </dialog>
</template>
