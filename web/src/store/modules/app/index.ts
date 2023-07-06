import { defineStore } from 'pinia'
import type { AppState, Language, Runtime, Theme } from './helper'
import { getLocalRuntime, getLocalSetting, setLocalSetting } from './helper'
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
      const action = url.searchParams.get('action')
      const params: any = {}
      url.searchParams.forEach((value, key) => {
        if (key !== 'action')
          params[key] = value
      })

      switch (action) {
        case 'verifyRegisterEmail':
          url.search = ''
          history.replaceState(null, '', url)
          window.$router.replace({ name: 'VerifyRegisterEmail', params })
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
