<script setup lang="ts">
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/outline'
import { ref } from 'vue'
import {
  useAjax,
  Dialog,
  DialogClose,
  DialogContent,
  DialogHeader,
  DialogTitle
} from '@concretecms/backendui'
import { useToolbarStore } from '../@stores/toolbar'
const toolbar = useToolbarStore()

const props = defineProps<{
  helpUrl: string
}>()

const helpDialogOpen = ref(false)
const sidebarContent = ref<string>()

function showModal(event: MouseEvent) {
  event.preventDefault()
  const { request } = useAjax()

  request({
    url: props.helpUrl,
    method: 'GET',
    skipResponseValidation: true,
    onSuccess: (data: { sidebar: string }) => {
      sidebarContent.value = data.sidebar
      helpDialogOpen.value = true
    },
  })
}
</script>

<template>
  <div
      :class="[toolbar.showTooltips && 'tooltip tooltip-bottom']"
      :data-tip="toolbar.showTooltips ? 'Help' : null"
  >
    <a
        :href="helpUrl"
        @click="showModal"
        class="c-toolbar-button"
        title="Help"
    >
      <QuestionMarkCircleIcon class="w-4 h-4" />
      <span v-if="toolbar.showTitles">Help</span>
    </a>
  </div>

  <Dialog v-model:open="helpDialogOpen">
    <DialogContent class="max-w-3xl">
      <DialogHeader>
        <DialogTitle>Help</DialogTitle>
      </DialogHeader>
      <!-- @TODO: make announcement items show up in here like in v9 //-->
      <div class="prose flex flex-col gap-2" v-html="sidebarContent" />
    </DialogContent>
  </Dialog>
</template>
