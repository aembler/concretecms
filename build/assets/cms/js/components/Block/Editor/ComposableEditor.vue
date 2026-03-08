<template>
  <div>
    EDITOR!!
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { useAjax } from '@concretecms/backendui'

const props = defineProps<{
  blockTypeId: number
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
