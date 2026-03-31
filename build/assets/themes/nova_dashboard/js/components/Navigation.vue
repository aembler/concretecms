<template>
  <template v-for="(group, groupIndex) in menuGroups" :key="`group-${groupIndex}`">
    <ul
      v-if="group.type === 'menu'"
      :class="menuClasses(group.size)"
    >
      <li
        v-for="item in group.items"
        :key="item.pageID ?? item.url ?? item.name"
      >
        <a
          :href="item.url"
          :class="linkClasses(item, group.size)"
        >
          <span
            v-if="hasRenderableIcon(item)"
            :class="iconContainerClasses(group.size)"
            aria-hidden="true"
          >
            <span
              v-if="isInlineSvgIcon(item.icon)"
              :class="iconInnerClasses(group.size)"
              v-html="item.icon.svg"
            />
            <i
              v-else-if="isFontAwesomeIcon(item.icon)"
              :class="[item.icon.className, iconInnerClasses(group.size)]"
            />
          </span>
          <span
            v-else
            :class="emptyIconClasses(group.size)"
            aria-hidden="true"
          />
          <span class="min-w-0 flex-1 truncate">{{ item.name }}</span>
        </a>

        <Navigation
          v-if="hasChildren(item)"
          :items="item.children"
          size="compact"
        />
      </li>
    </ul>

    <ul
      v-else
      class="menu w-full rounded-box p-0"
    >
      <li class="menu-title my-1 p-0">
        <div
          class="h-px w-full bg-base-300"
          role="separator"
        />
      </li>
    </ul>
  </template>
</template>

<script setup>
import { computed } from 'vue'

defineOptions({
  name: 'Navigation',
})

const props = defineProps({
  items: {
    type: Array,
    required: true,
  },
  size: {
    type: String,
    default: 'default',
  },
})

const isDivider = (item) => item?.type === 'divider'

const isWelcomeItem = (item) => typeof item?.url === 'string' && /\/dashboard\/welcome(?:\/|$)/.test(item.url)

const hasChildren = (item) => Array.isArray(item.children) && item.children.length > 0

const isInlineSvgIcon = (icon) => icon?.type === 'inline-svg' && typeof icon.svg === 'string' && icon.svg.length > 0

const isFontAwesomeIcon = (icon) => icon?.type === 'font-awesome' && typeof icon.className === 'string' && icon.className.length > 0

const hasRenderableIcon = (item) => isInlineSvgIcon(item.icon) || isFontAwesomeIcon(item.icon)

const defaultMenuSize = computed(() => (props.size === 'compact' ? 'sm' : 'md'))

const menuClasses = (size) => {
  if (size === 'sm') {
    return 'menu menu-sm w-full gap-1 rounded-box p-0 pl-4'
  }

  if (size === 'lg') {
    return 'menu menu-lg w-full gap-1 rounded-box p-0'
  }

  return 'menu menu-md w-full gap-1 rounded-box p-0'
}

const iconContainerClasses = (size) => [
  'flex shrink-0 items-center justify-center',
  size === 'lg' ? 'h-10 w-10' : 'h-8 w-10',
]

const iconInnerClasses = (size) => [
  'inline-flex items-center justify-center',
  size === 'lg' ? 'h-6 w-6 [&_svg]:h-6 [&_svg]:w-6' : 'h-5 w-5 [&_svg]:h-5 [&_svg]:w-5',
]

const emptyIconClasses = (size) => [
  'shrink-0 place-self-center rounded-md bg-base-300/60',
  size === 'lg' ? 'h-5 w-5' : 'h-4 w-4',
]

const menuGroups = computed(() => {
  const groups = []
  let currentItems = []

  const pushCurrentItems = () => {
    if (!currentItems.length) {
      return
    }

    const isFirstGroup = groups.length === 0
    const hasWelcome = currentItems.some(isWelcomeItem)
    const size = isFirstGroup && props.size !== 'compact' && hasWelcome
      ? 'lg'
      : defaultMenuSize.value

    groups.push({
      type: 'menu',
      size,
      items: currentItems,
    })

    currentItems = []
  }

  for (const item of props.items) {
    if (isDivider(item)) {
      pushCurrentItems()
      groups.push({type: 'divider'})
      continue
    }

    currentItems.push(item)
  }

  pushCurrentItems()

  return groups
})

const linkClasses = (item, size) => [
  '!grid grid-cols-[2.5rem_minmax(0,1fr)] items-center rounded-lg px-3 transition-colors',
  size === 'lg' ? '!py-1' : '!py-0.5',
  item.isActive || item.isActiveParent
    ? 'menu-active'
    : 'text-base-content/70 hover:bg-base-200 hover:text-base-content',
]
</script>
