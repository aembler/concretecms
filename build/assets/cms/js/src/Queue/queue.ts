export type OperationStatus = 'queued' | 'running' | 'done' | 'failed'

export type QueueFinishStatus = OperationStatus | 'removed'

export interface Operation {
  id: string
  type: string
  status: OperationStatus
}

export type QueueState<T extends Operation> = {
  queue: T[]
  currentId: string | null
}

type FinishQueueItemOptions = {
  removeOnDone?: boolean
  removeOnFailed?: boolean
}

export function current<T extends Operation>(state: QueueState<T>): T | null {
  if (!state.currentId) {
    return null
  }

  return state.queue.find((item) => item.id === state.currentId) ?? null
}

export function next<T extends Operation>(state: QueueState<T>): T | null {
  if (state.currentId && current(state)) {
    return current(state)
  }

  if (state.currentId && !current(state)) {
    state.currentId = null
  }

  const nextItem = state.queue.find((item) => item.status === 'queued') ?? null
  if (!nextItem) {
    return null
  }

  nextItem.status = 'running'
  state.currentId = nextItem.id

  return nextItem
}

export function enqueue<T extends Operation>(state: QueueState<T>, item: T): T {
  state.queue.push(item)
  next(state)

  return item
}

export function finish<T extends Operation>(
  state: QueueState<T>,
  id: string,
  status: QueueFinishStatus,
  options: FinishQueueItemOptions = {},
): T | null {
  const existingItem = state.queue.find((item) => item.id === id) ?? null
  if (!existingItem) {
    return null
  }

  const removeOnDone = options.removeOnDone ?? false
  const removeOnFailed = options.removeOnFailed ?? false
  const shouldRemove = status === 'removed'
    || (status === 'done' && removeOnDone)
    || (status === 'failed' && removeOnFailed)

  if (shouldRemove) {
    state.queue = state.queue.filter((item) => item.id !== id)
  } else {
    existingItem.status = status
  }

  if (state.currentId === id) {
    state.currentId = null
  }

  next(state)

  return shouldRemove ? null : existingItem
}
