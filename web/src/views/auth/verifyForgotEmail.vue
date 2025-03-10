<script setup lang='tsx'>
import { NButton, NCard, NIcon, NSpin } from 'naive-ui'
import { ref } from 'vue'
import { CheckmarkCircle, CloseCircle } from '@vicons/ionicons5'
import { useRoute, useRouter } from 'vue-router'
import { verifyForgotFromEmail } from '@/api'
import { isString } from '@/utils/is'

const route = useRoute()
const router = useRouter()
let { email, token, verifyToken }: any = route.params
if (!isString(email))
  email = null
if (!isString(token))
  token = null
if (!isString(verifyToken))
  verifyToken = null

const status = ref((email === null || token === null || verifyToken === null) ? 'paramError' : 'normal')
const loading = ref(false)
const success = ref(false)
const failReason = ref('')

async function verify() {
  loading.value = true
  try {
    await verifyForgotFromEmail(email ?? '', token ?? '', verifyToken ?? '', (response) => {
      failReason.value = response.data.message || 'Error'
    })
    success.value = true
    setTimeout(() => {
      router.replace({ path: '/auth/login' })
    }, 3000)
  }
  catch (e: any) {
    success.value = false
    failReason.value = e?.message ?? '网络错误'
    throw e
  }
  finally {
    status.value = 'result'
  }
}
</script>

<template>
  <div class="wrap">
    <NCard title="验证邮箱" header-style="text-align:center" class="mt-4">
      <div class="text-center leading-loose">
        <template v-if="'paramError' === status">
          <p>参数错误</p>
        </template>
        <template v-else>
          <p>你的邮箱：<b class="text-red-500" v-text="email" /></p>
          <div>
            <NSpin v-if="'normal' === status" :show="loading">
              <NButton type="info" size="large" @click="verify">
                重置密码
              </NButton>
            </NSpin>
            <template v-if="'result' === status">
              <p v-if="success" class="text-2xl text-green-600">
                <NIcon :size="36" :component="CheckmarkCircle" class="align-middle" />
                <span class="inline-block align-middle">密码重置成功！</span>
              </p>
              <div v-else class="text-2xl text-red-600">
                <p>
                  <NIcon :size="36" :component="CloseCircle" class="align-middle" />
                  <span class="inline-block align-middle">验证失败！</span>
                </p>
                <p v-text="failReason" />
              </div>
            </template>
          </div>
        </template>
      </div>
    </NCard>
  </div>
</template>
