import { ss } from '@/utils/storage'

const LOCAL_NAME = 'userStorage'

export interface UserInfo {
  recordId: string
  email: string
  phone: string
  nickname: string
  avatar: string | null
  inviterId: number
  inviterTime: number
  invitationCode: string
  registerTime: number
}

export interface UserState {
  userInfo: UserInfo
}

export function defaultSetting(): UserState {
  return {
    userInfo: {
      recordId: '',
      email: '',
      phone: '',
      nickname: '',
      avatar: null,
      inviterId: 0,
      inviterTime: 0,
      invitationCode: '',
      registerTime: 0,
    },
  }
}

export function getLocalState(): UserState {
  const localSetting: UserState | undefined = ss.get(LOCAL_NAME)
  return { ...defaultSetting(), ...localSetting }
}

export function setLocalState(setting: UserState): void {
  ss.set(LOCAL_NAME, setting)
}
