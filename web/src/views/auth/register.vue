<script setup lang='ts'>
import { NButton, NCard, NResult, NTabPane, NTabs } from 'naive-ui'
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { EmailRegister } from './components'
import { useAuthStore } from '@/store'

const router = useRouter()
const authStore = useAuthStore()

if (authStore.hasToken())
  router.replace({ path: '/' })

const success = ref(false)

watch(success, (val) => {
  if (val)
    delayJump()
})

const jumpCountDown = ref(0)
let jumpTimer: NodeJS.Timer | null = null

function delayJump() {
  jumpCountDown.value = 3
  jumpTimer = setInterval(() => {
    if (--jumpCountDown.value === 0) {
      router.replace({ path: '/' })
      if (jumpTimer)
        clearInterval(jumpTimer)
    }
  }, 1000)
}
</script>

<template>
  <div class="wrap">
    <NCard title="注册" header-style="text-align:center" class="mt-4">
      <NResult v-if="success" status="success" title="注册成功" :description="`${jumpCountDown}秒后跳转到首页...`">
        <template #footer>
          <NButton @click="router.replace({ path: '/' })">
            立即跳转
          </NButton>
        </template>
      </NResult>
      <template v-else>
        <NTabs
          class="card-tabs"
          default-value="email"
          size="large"
          animated
        >
          <NTabPane name="email" tab="邮箱注册">
            <EmailRegister v-model:success="success" />
          </NTabPane>
        </NTabs>
      </template>
    </NCard>
  </div>
</template>
