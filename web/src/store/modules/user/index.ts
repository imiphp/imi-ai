import { defineStore } from 'pinia'
import { useAuthStore } from '../auth'
import type { UserInfo, UserState } from './helper'
import { defaultSetting, getLocalState, setLocalState } from './helper'
import { authInfo } from '@/api'

export const useUserStore = defineStore('user-store', {
  state: (): UserState => getLocalState(),
  actions: {
    updateUserInfo(userInfo: Partial<UserInfo>) {
      this.userInfo = { ...this.userInfo, ...userInfo }
      this.recordState()
    },

    resetUserInfo() {
      this.userInfo = { ...defaultSetting().userInfo }
      this.recordState()
    },

    recordState() {
      setLocalState(this.$state)
    },

    async updateUserInfoFromApi() {
      if (useAuthStore().hasToken()) {
        const response = await authInfo()
        this.updateUserInfo(response.data)
      }
    },
  },
})
