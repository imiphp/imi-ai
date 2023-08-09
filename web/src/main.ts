import { createApp, nextTick } from 'vue'
import App from './App.vue'
import { setupI18n } from './locales'
import { setupAssets, setupScrollbarStyle } from './plugins'
import { setupStore, useAppStoreWithOut, useUserStore } from './store'
import { setupRouter } from './router'

async function bootstrap() {
  const app = createApp(App)
  setupAssets()

  setupScrollbarStyle()

  setupStore(app)

  setupI18n(app)

  await setupRouter(app)

  nextTick(() => {
    useUserStore().updateUserInfoFromApi()
  })

  app.mount('#app')

  useAppStoreWithOut().parseAction()
}

bootstrap()
