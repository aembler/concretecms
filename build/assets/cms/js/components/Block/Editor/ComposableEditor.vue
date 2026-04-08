<template>
  <div>
    EDITOR!!
  </div>
</template>

<script lang="ts">
import type { BlockEditorMeta } from '../../../stores/block-editor-registry'

export const blockEditorMeta: BlockEditorMeta = {
  pageContentMode: 'preserve',
  placement: 'page',
  editorContentSource: 'none',
}
</script>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAjax } from '@concretecms/backendui'

const props = defineProps<{
  blockTypeId: number
  blockId?: string | number
  areaHandle?: string
  pageId?: string | number
  contentHtml?: string | null
  contentEl?: HTMLElement | null
}>()

const { request } = useAjax()

onMounted(async () => {
  request({
    url: CCM_DISPATCHER_FILENAME + `/ccm/block_types/manifest/${props.blockTypeId}`,
    method: 'GET',
    onSuccess: (data) => {
      console.log(data)
    },
  })
})
</script>
