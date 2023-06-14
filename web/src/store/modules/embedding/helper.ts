import { ss } from '@/utils/storage'

const LOCAL_NAME = 'embeddingStorage'

export function defaultState(): Embedding.State {
  return { currentProject: null }
}

export function getLocalState(): Embedding.State {
  return defaultState()
}

export function setLocalState(state: Embedding.State) {
  ss.set(LOCAL_NAME, state)
}
