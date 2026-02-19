<template>
  <HotSpot
      :item-id="id"
      :menu-id="menuId"
      hover-outline-color="outline-concrete-green"
      active-outline-color="outline-concrete-green"
      active-bg-class="bg-concrete-green/30"
      @dblclick="editBlock"
  >
    <template #badge>
      {{ name }}
    </template>
    <template #menu>
      <Menu
          :variants="parsedVariants"
          :selected-variant="selectedVariant"
          :id="menuId"
          @edit="editBlock"
      >
      </Menu>
    </template>
    <div v-if="editMode">
      <component :is="currentEditorComponent" :block-type-id="parseBlockType.id" v-if="editMode" />
    </div>
    <slot />
  </HotSpot>
</template>

<script setup lang="ts">
import { computed, ref } from "vue"
import HotSpot from "./Ui/HotSpot.vue"
import Menu from "./Block/Menu.vue";
import { useParsedJsonProp } from '@concretecms/backendui'
import { useBlockEditorRegistry } from '@concretecms/backendui'

const editMode = ref(false)

const props = defineProps({
  id: String,
  name: String,
  variants: String | Array<{ file: String; name: String }>,
  blocktype: String | Object,
  selectedVariant: String
})

const parsedVariants = useParsedJsonProp(props.variants)
const parseBlockType = useParsedJsonProp(props.blocktype)

let menuId = computed(() => props.id + '-menu')

function editBlock() {
  editMode.value = true
}

const registry = useBlockEditorRegistry();
const currentEditorComponent = computed(() => {
  return registry.getEditorComponent(parseBlockType.editor.component);
});

</script>
