<template>
  <div
      ref="rootEl"
      :class="[
      'select-none z-1 relative cursor-pointer outline-3 transition-all duration-200',
      outlineColor,
    ]"
  >
    <!-- Optional floating badge -->
    <div
        v-if="$slots.badge"
        :class="[
        'absolute top-0 left-1/2 -translate-x-1/2 pointer-events-none',
        isVisible ? 'animate-hotSpotBadge' : 'opacity-0',
        'z-3 shadow-sm text-xs font-semibold uppercase rounded-full py-1 px-2 inline-block bg-concrete-green'
      ]"
    >
      <slot name="badge" />
    </div>

    <!-- Menu: stays absolutely positioned, with dynamic top -->
    <!-- need data-theme=light for daisyUI variables -->
    <teleport :to="uiStore.menuContainer">
      <Transition
          enter-active-class="transition-opacity duration-200"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition-opacity duration-200"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
      >
        <div
            :id="menuId"
            v-show="true"
            :class="[
        'flex absolute z-50 pointer-events-auto -translate-x-1/2 transition-opacity duration-200',
        isStoreActiveMatch ? 'opacity-100 visible' : 'opacity-0 invisible'
      ]"
            ref="menuEl"
            :style="{ top: menuTop, left: menuLeft }"
        >
          <slot name="menu" />
        </div>
      </Transition>
    </teleport>


    <slot />

    <div
        :class="[
        'absolute inset-0 z-10 pointer-events-auto transition-all duration-200',
        isStoreActiveMatch && activeBgClass
      ]"
    ></div>
    <div class="absolute inset-0 cursor-pointer z-2"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { useUiStore } from '@concretecms/backendui'

const props = withDefaults(
    defineProps<{
      itemId: string
      menuId: string
      hoverOutlineColor?: string
      activeOutlineColor?: string
      activeBgClass?: string
    }>(),
    {
      hoverOutlineColor: 'concrete-green',
      activeOutlineColor: 'concrete-green',
      activeBgClass: '',
    }
)

const uiStore = useUiStore()
const rootEl = ref<HTMLElement | null>(null)
const menuEl = ref<HTMLElement | null>(null)

const menuTop = ref('0px')
const menuLeft = ref('0px')

const isStoreActiveMatch = computed(() => uiStore.clickProxy.activeElementId === props.itemId)
const isStoreDoubleClickMatch = computed(() => uiStore.clickProxy.doubleClickedElementId === props.itemId)
const isStoreHoverMatch = computed(() => {
  if (!uiStore.clickProxy.activeElementId) {
    return uiStore.clickProxy.hoverElementId === props.itemId
  }
})
const isVisible = computed(() => isStoreActiveMatch.value || isStoreHoverMatch.value)

const outlineColor = computed(() => {
  if (isStoreActiveMatch.value) return props.activeOutlineColor
  if (isStoreHoverMatch.value) return props.hoverOutlineColor
  return 'outline-transparent'
})

const toolbarHeight = 72
const verticalDifferenceAbove = 10
const verticalDifferenceBelow = 10

async function updateMenuTop() {
  if (!rootEl.value || !menuEl.value || !isStoreActiveMatch.value) return

  const rect = rootEl.value.getBoundingClientRect()
  const xOnPage = rect.left
  const yOnPage = rect.top
  const menuHeight = menuEl.value.offsetHeight
  const menuWidth = menuEl.value.offsetWidth
  const elementWidth = rect.width

  const menuLeftTmp = xOnPage + elementWidth / 2
  let menuTopTmp = yOnPage - menuHeight - verticalDifferenceAbove
  if (menuTopTmp < toolbarHeight) {
    menuTopTmp = toolbarHeight + verticalDifferenceBelow
  }
  menuTop.value = menuTopTmp + 'px'
  menuLeft.value = menuLeftTmp + 'px'
}

const emit = defineEmits(['dblclick'])

onMounted(() => {
  updateMenuTop()
})
watch([() => uiStore.scroll.y, () => isStoreActiveMatch.value], updateMenuTop)
watch(() => isStoreActiveMatch.value, () => {
  uiStore.clickProxy.activeElementMenuId = props.menuId
})
watch(() => isStoreDoubleClickMatch.value, (value) => {
  if (value) {
    emit('dblclick')
  }
})
</script>
