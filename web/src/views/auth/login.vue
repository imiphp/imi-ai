<script setup lang='tsx'>
import { NCard, NTabPane, NTabs, useMessage } from 'naive-ui'
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Login } from './components'
import { useAuthStore } from '@/store'

const router = useRouter()
const authStore = useAuthStore()
const message = useMessage()

if (authStore.hasToken())
  router.replace({ path: '/' })

const success = ref(false)

watch(success, (val) => {
  if (val) {
    message.success('登录成功')
    const url = authStore.getRemoveLoginRedirectUrl()
    if (url)
      location.href = url
    else
      router.replace({ path: '/' })
  }
})
</script>

<template>
  <div class="wrap">
    <NCard title="登录" header-style="text-align:center" class="mt-4">
      <NTabs
        class="card-tabs"
        default-value="login"
        size="large"
        animated
      >
        <NTabPane name="login" tab="密码登录">
          <Login v-model:success="success" />
        </NTabPane>
      </NTabs>
    </NCard>
  </div>
</template>
