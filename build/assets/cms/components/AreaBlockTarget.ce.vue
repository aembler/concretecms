<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watchEffect } from 'vue'
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
const isDragActive = computed(() => Boolean(pageState.value?.addContentDragActive) && isDragInProgress.value)
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
  if (isPointerInsideTarget(pointer, hitboxPaddingX, hitboxPaddingY)) {
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
  if (stickyReleaseTimer) {
    clearTimeout(stickyReleaseTimer)
    stickyReleaseTimer = null
  }
})
</script>

<template>
  <div
    ref="targetRef"
    class="ccm-area-block-target"
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
      class="ccm-area-block-target-pill px-6 shadow-sm text-xs font-semibold uppercase rounded-full py-1 inline-block bg-concrete-green"
    >
      + Add
    </div>
  </div>
</template>

<style>
.ccm-area-block-target {
  height: 0;
  overflow: hidden;
  position: relative;
  transition: height 180ms cubic-bezier(0.22, 1, 0.36, 1), background-color 180ms ease, border-radius 180ms ease, border-color 180ms ease;
  will-change: height, background-color, border-radius, border-color;
}

.ccm-area-block-target.is-drag-active {
  height: 10px;
  border-radius: 4px;
  border: 2px solid var(--color-concrete-green);
  margin: 4px 0;

  .ccm-area-block-target-pill {
    opacity: 0;
  }
}

.ccm-area-block-target.is-drag-active:hover,
.ccm-area-block-target.is-active-target {
  height: 30px;
  background: var(--color-concrete-green-heavy);
  border-radius: 0;
  border-color: transparent;

  .ccm-area-block-target-pill {
    opacity: 1;
  }
}

.ccm-area-block-target-pill {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translateY(-50%) translateX(-50%);
  will-change: opacity;
  transition: height 180ms cubic-bezier(0.22, 1, 0.36, 1);
}
</style>
