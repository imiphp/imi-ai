import { QAStatus } from '.'
import { ss } from '@/utils/storage'

const LOCAL_NAME = 'chatStorage'

export function defaultState(): Chat.ChatState {
  const id = ''
  return {
    active: null,
    usingContext: true,
    history: [{ id, title: 'New Chat', isEdit: false, createTime: 0, updateTime: 0, qaStatus: QAStatus.ASK, tokens: 0 }],
    chat: [{ id, data: [] }],
    page: 1,
    pageSize: 15,
  }
}

export function getLocalState(): Chat.ChatState {
  // const localState = ss.get(LOCAL_NAME)
  // return { ...defaultState(), ...localState }
  return defaultState()
}

export function setLocalState(state: Chat.ChatState) {
  ss.set(LOCAL_NAME, state)
}
