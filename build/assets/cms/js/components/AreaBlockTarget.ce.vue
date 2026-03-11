<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch, watchEffect } from 'vue'
import { useConcreteUiStore } from '../stores/concrete-ui'

const props = withDefaults(defineProps<{
  areaId?: number | string
  pageId?: number | string
  areaHandle?: string
  afterBlockId?: number | string
  targetIndex?: number | string
}>(), {
  areaId: 0,
  pageId: 0,
  areaHandle: '',
  afterBlockId: 0,
  targetIndex: 0,
})

const uiStore = useConcreteUiStore()
const targetRef = ref<HTMLElement | null>(null)
let stickyReleaseTimer: ReturnType<typeof setTimeout> | null = null
let activationFrameId: number | null = null
const justActivatedDrag = ref(false)

// 1) Expanded hitbox (easy to disable independently)
const ENABLE_EXPANDED_HITBOX = true
const HITBOX_PADDING_X = 6
const HITBOX_PADDING_Y = 12

// 2) Sticky release/hysteresis (easy to disable independently)
const ENABLE_STICKY_RELEASE = true
const STICKY_RELEASE_PADDING_X = 10
const STICKY_RELEASE_PADDING_Y = 22
const STICKY_RELEASE_DELAY_MS = 120

const pageState = computed(() => (uiStore.page as any))
const isDragInProgress = computed(() => Boolean(pageState.value?.addContentDragInProgress))
const isInteractionsEnabled = computed(() => Boolean(pageState.value?.interactionsEnabled ?? true))
const isDragActive = computed(() => isInteractionsEnabled.value && Boolean(pageState.value?.addContentDragActive) && isDragInProgress.value)
const draggedItem = computed(() => pageState.value?.addContentDraggedItem ?? null)
const isValidDraggedBlockType = computed(() => draggedItem.value?.type === 'blockType')

const ownAreaId = computed(() => Number(props.areaId || 0))
const ownPageId = computed(() => Number(props.pageId || 0))
const ownAfterBlockId = computed(() => Number(props.afterBlockId || 0))
const ownTargetIndex = computed(() => Number(props.targetIndex || 0))
const ownAreaHandle = computed(() => String(props.areaHandle || ''))

const isActiveTarget = computed(() => {
  const dropTarget = pageState.value?.addContentDropTarget
  if (!dropTarget) {
    return false
  }

  return Number(dropTarget.areaId || 0) === ownAreaId.value
    && Number(dropTarget.pageId || 0) === ownPageId.value
    && String(dropTarget.areaHandle || '') === ownAreaHandle.value
    && Number(dropTarget.afterBlockId || 0) === ownAfterBlockId.value
})

watch(isDragActive, (active) => {
  if (activationFrameId !== null) {
    cancelAnimationFrame(activationFrameId)
    activationFrameId = null
  }

  if (!active) {
    justActivatedDrag.value = false
    return
  }

  // Give one paint to the base "drag-active" height transition before
  // promoting a hovered target to the expanded active-target state.
  justActivatedDrag.value = true
  activationFrameId = requestAnimationFrame(() => {
    justActivatedDrag.value = false
    activationFrameId = null
  })
})

function isPointerInsideTarget(
  pointer: { x: number; y: number } | null,
  paddingX = 0,
  paddingY = 0,
): boolean {
  if (!pointer || !targetRef.value) {
    return false
  }

  const rect = targetRef.value.getBoundingClientRect()
  return pointer.x >= (rect.left - paddingX)
    && pointer.x <= (rect.right + paddingX)
    && pointer.y >= (rect.top - paddingY)
    && pointer.y <= (rect.bottom + paddingY)
}

function claimDropTarget() {
  if (stickyReleaseTimer) {
    clearTimeout(stickyReleaseTimer)
    stickyReleaseTimer = null
  }

  pageState.value.addContentDropTarget = {
    areaId: ownAreaId.value,
    pageId: ownPageId.value,
    areaHandle: ownAreaHandle.value,
    afterBlockId: ownAfterBlockId.value,
    targetIndex: ownTargetIndex.value,
  }
}

function releaseDropTarget() {
  if (stickyReleaseTimer) {
    clearTimeout(stickyReleaseTimer)
    stickyReleaseTimer = null
  }

  if (!isActiveTarget.value) {
    return
  }

  pageState.value.addContentDropTarget = null
}

watchEffect(() => {
  if (!isDragInProgress.value || !isDragActive.value || !isValidDraggedBlockType.value) {
    releaseDropTarget()
    return
  }

  const pointer = pageState.value?.addContentDragPointer ?? null
  const hitboxPaddingX = ENABLE_EXPANDED_HITBOX ? HITBOX_PADDING_X : 0
  const hitboxPaddingY = ENABLE_EXPANDED_HITBOX ? HITBOX_PADDING_Y : 0
  if (!justActivatedDrag.value && isPointerInsideTarget(pointer, hitboxPaddingX, hitboxPaddingY)) {
    claimDropTarget()
    return
  }

  if (ENABLE_STICKY_RELEASE && isActiveTarget.value) {
    if (isPointerInsideTarget(pointer, STICKY_RELEASE_PADDING_X, STICKY_RELEASE_PADDING_Y)) {
      if (stickyReleaseTimer) {
        clearTimeout(stickyReleaseTimer)
        stickyReleaseTimer = null
      }
      return
    }

    if (!stickyReleaseTimer) {
      stickyReleaseTimer = setTimeout(() => {
        stickyReleaseTimer = null
        if (isActiveTarget.value) {
          pageState.value.addContentDropTarget = null
        }
      }, STICKY_RELEASE_DELAY_MS)
    }
    return
  }

  releaseDropTarget()
})

onBeforeUnmount(() => {
  if (activationFrameId !== null) {
    cancelAnimationFrame(activationFrameId)
    activationFrameId = null
  }
  if (stickyReleaseTimer) {
    clearTimeout(stickyReleaseTimer)
    stickyReleaseTimer = null
  }
})
</script>

<template>
  <div
    ref="targetRef"
    class="concrete-area-block-target"
    :class="[
      isDragActive ? 'is-drag-active' : '',
      isActiveTarget ? 'is-active-target' : '',
    ]"
    :data-area-id="ownAreaId"
    :data-page-id="ownPageId"
    :data-area-handle="ownAreaHandle"
    :data-after-block-id="ownAfterBlockId"
    :data-target-index="ownTargetIndex"
  >
    <div
      class="concrete-area-block-target-pill"
    >
      + Add
    </div>
  </div>
</template>
