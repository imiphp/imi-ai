import { computed } from 'vue'
import { dateEnUS, dateKoKR, dateRuRU, dateZhCN, enUS, koKR, zhCN, zhTW } from 'naive-ui'
import datezhTW from 'naive-ui/es/locales/date/zhTW'
import { useAppStore } from '@/store'
import { setLocale } from '@/locales'

export function useLanguage() {
  const appStore = useAppStore()

  const language = computed(() => {
    switch (appStore.language) {
      case 'en-US':
        setLocale('en-US')
        return enUS
      case 'ru-RU':
        setLocale('ru-RU')
        return enUS
      case 'ko-KR':
        setLocale('ko-KR')
        return koKR
      case 'zh-CN':
        setLocale('zh-CN')
        return zhCN
      case 'zh-TW':
        setLocale('zh-TW')
        return zhTW
      default:
        setLocale('zh-CN')
        return zhCN
    }
  })

  const dateLocale = computed(() => {
    switch (appStore.language) {
      case 'en-US':
        return dateEnUS
      case 'ru-RU':
        return dateRuRU
      case 'ko-KR':
        return dateKoKR
      case 'zh-CN':
        return dateZhCN
      case 'zh-TW':
        return datezhTW
      default:
        return dateZhCN
    }
  })

  return { language, dateLocale }
}
