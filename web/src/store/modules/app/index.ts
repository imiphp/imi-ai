import { defineStore } from 'pinia'
import type { AppState, Language, Runtime, Theme } from './helper'
import { getLocalRuntime, getLocalSetting, setLocalSetting } from './helper'
import { router } from '@/router'
import { store } from '@/store'

export const useAppStore = defineStore('app-store', {
  state: (): AppState => getLocalSetting(),
  actions: {
    setSiderCollapsed(collapsed: boolean) {
      this.siderCollapsed = collapsed
      this.recordState()
    },

    setTheme(theme: Theme) {
      this.theme = theme
      this.recordState()
    },

    setLanguage(language: Language) {
      if (this.language !== language) {
        this.language = language
        this.recordState()
      }
    },

    recordState() {
      setLocalSetting(this.$state)
    },

    parseAction() {
      const url = new URL(location.toString())
      if (!url.searchParams.has('action'))
        return

      switch (url.searchParams.get('action')) {
        case 'verifyRegisterEmail':
          router.push({ name: 'VerifyRegisterEmail' })
          break
      }
    },
  },
})

export function useAppStoreWithOut() {
  return useAppStore(store)
}

export const useRuntimeStore = defineStore('runtime-store', {
  state: (): Runtime => getLocalRuntime(),
})
