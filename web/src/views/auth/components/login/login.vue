<script setup lang='ts'>
import type { FormInst } from 'naive-ui'
import { NButton, NForm, NFormItemRow, NInput } from 'naive-ui'
import type { Ref } from 'vue'
import { computed, ref } from 'vue'

import { useRouter } from 'vue-router'
import { VCode } from '@/components/common'
import { login } from '@/api'
import { useAuthStore, useUserStore } from '@/store'
import { hashPassword } from '@/utils/functions'

interface Props {
  success: Boolean
}

interface Emit {
  (e: 'update:success', success: Boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const success = computed({
  get: () => props.success,
  set: (success: Boolean) => emit('update:success', success),
})

const userStore = useUserStore()
const authStore = useAuthStore()
const router = useRouter()

const form = ref<FormInst | null>(null)
const formData = ref({
  account: '',
  password: '',
  vcode: '',
  vcodeToken: '',
})
const formRules = ref({
  account: {
    required: true,
    message: '请输入账号',
  },
  password: {
    required: true,
    message: '请输入密码',
  },
  vcode: {
    required: true,
    message: '请输入图形验证码',
  },
})
const loading = ref(false)
const vcode = ref<Ref | null>(null)
const inputVcode = ref<Ref | null>(null)

async function handleClickLogin() {
  form.value?.validate().then(async () => {
    try {
      loading.value = true
      const data = { ...formData.value }
      data.password = hashPassword(data.password)
      const response = await login(data)
      userStore.updateUserInfo(response.member)
      authStore.setToken(response.token)
      success.value = true
    }
    catch (e) {
      vcode.value.loadVCode()
      formData.value.vcode = ''
      inputVcode.value.focus()
      throw e
    }
    finally {
      loading.value = false
    }
  })
}

function handleClickRegister() {
  router.push({ name: 'Register' })
}
</script>

<template>
  <NForm ref="form" :model="formData" :rules="formRules">
    <NFormItemRow path="account" label="账号">
      <NInput v-model:value="formData.account" placeholder="邮箱/手机号" />
    </NFormItemRow>
    <NFormItemRow path="password" label="登录密码">
      <NInput v-model:value="formData.password" type="password" />
    </NFormItemRow>
    <NFormItemRow path="vcode" label="图形验证码">
      <NInput ref="inputVcode" v-model:value="formData.vcode" />
      <VCode ref="vcode" v-model:token="formData.vcodeToken" />
    </NFormItemRow>
    <NButton attr-type="submit" type="primary" block strong :loading="loading" @click="handleClickLogin">
      登录
    </NButton>
    <NButton secondary block :loading="loading" @click="handleClickRegister">
      没有账号？点击注册
    </NButton>
  </NForm>
</template>
