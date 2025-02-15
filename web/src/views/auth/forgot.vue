<script setup lang='tsx'>
import { NCard, NTabPane, NTabs, useMessage } from 'naive-ui'
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { EmailForgot } from './components'
import { useAuthStore } from '@/store'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const message = useMessage()

const invitationCode = route.params.invitationCode?.toString()

if (authStore.hasToken())
  router.replace({ path: '/' })

const success = ref(false)

watch(success, (val) => {
  if (val) {
    message.success('找回密码成功')
    router.replace({ path: '/auth/login' })
  }
})
</script>

<template>
  <div class="wrap">
    <NCard title="忘记密码" header-style="text-align:center" class="mt-4">
      <NTabs
        class="card-tabs"
        default-value="email"
        size="large"
        animated
      >
        <NTabPane name="email" tab="邮箱找回">
          <EmailForgot v-model:success="success" :invitation-code="invitationCode" />
        </NTabPane>
      </NTabs>
    </NCard>
  </div>
</template>
