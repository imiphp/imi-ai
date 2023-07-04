<script setup lang='ts'>
import type { FormInst, FormItemRule } from 'naive-ui'
import { NButton, NForm, NFormItemRow, NInput } from 'naive-ui'
import type { Ref } from 'vue'
import { computed, nextTick, ref } from 'vue'

import { VCode } from '@/components/common'
import { hashPassword } from '@/utils/functions'
import { emailRegister, sendRegisterEmail } from '@/api'
import { useAuthStore, useUserStore } from '@/store'

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

const form = ref<FormInst | null>(null)
const formData = ref({
  email: '',
  password: '',
  confirmPassword: '',
  vcode: '',
  vcodeToken: '',
  emailVCodeToken: '',
  emailVCode: '',
})
const sendRegisterEmailRules = {
  email: {
    required: true,
    message: '请输入邮箱地址',
  },
  password: {
    required: true,
    message: '请输入密码',
  },
  confirmPassword: [
    {
      required: true,
      message: '请再次输入密码',
      trigger: ['blur', 'password-input'],
    },
    {
      validator: (rule: FormItemRule, value: string) => value === formData.value.password,
      message: '两次密码输入不一致',
      trigger: ['blur', 'password-input'],
    },
  ],
  vcode: {
    required: true,
    message: '请输入图形验证码',
  },
}
const registerRules = {
  ...sendRegisterEmailRules,
  emailVCode: {
    required: true,
    message: '请输入邮件验证码',
  },
}
const formRules = ref(sendRegisterEmailRules)
const emailSent = ref(false)
const loading = ref(false)
const vcode = ref<Ref | null>(null)
const inputVcode = ref<Ref | null>(null)
const inputEmailVCode = ref<Ref | null>(null)
const sendButtonDisabled = ref(false)
const sendButtonTitleOrigin = '发送验证码'
const sendButtonTitle = ref(sendButtonTitleOrigin)

let sendButtonTitleCountdown = 0
let sendButtonTitleCountdownTimer: NodeJS.Timer | null = null

async function handleClickSendEmail() {
  formRules.value = sendRegisterEmailRules
  await form.value?.validate().then(async () => {
    try {
      loading.value = true
      const { confirmPassword: _, ...data } = { ...formData.value }
      data.password = hashPassword(data.password)
      const response = await sendRegisterEmail({ ...data })
      formData.value.emailVCodeToken = response.token
      formRules.value = registerRules
      emailSent.value = true
      sendButtonDisabled.value = true
      sendButtonTitleCountdown = 60
      sendButtonTitleCountdownTimer = setInterval(() => {
        sendButtonTitle.value = `${sendButtonTitleCountdown--}秒后可重发`
        if (sendButtonTitleCountdown === 0) {
          sendButtonTitle.value = sendButtonTitleOrigin
          sendButtonDisabled.value = false
          if (sendButtonTitleCountdownTimer) {
            clearInterval(sendButtonTitleCountdownTimer)
            sendButtonTitleCountdownTimer = null
          }
        }
      }, 1000)
      nextTick(() => {
        inputEmailVCode.value?.focus()
      })
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
async function handleClickRegister() {
  formRules.value = registerRules
  form.value?.validate().then(async () => {
    try {
      loading.value = true
      const data = {
        email: formData.value.email,
        vcodeToken: formData.value.emailVCodeToken,
        vcode: formData.value.emailVCode,
      }
      const response = await emailRegister(data)
      userStore.updateUserInfo(response.member)
      authStore.setToken(response.token)
      success.value = true
    }
    finally {
      loading.value = false
    }
  })
}
</script>

<template>
  <NForm ref="form" :model="formData" :rules="formRules">
    <NFormItemRow path="email" label="邮箱地址">
      <NInput v-model:value="formData.email" />
    </NFormItemRow>
    <NFormItemRow path="password" label="登录密码">
      <NInput v-model:value="formData.password" type="password" />
    </NFormItemRow>
    <NFormItemRow path="confirmPassword" :first="true" label="确认密码">
      <NInput v-model:value="formData.confirmPassword" type="password" />
    </NFormItemRow>
    <NFormItemRow path="vcode" label="图形验证码">
      <NInput ref="inputVcode" v-model:value="formData.vcode" />
      <VCode ref="vcode" v-model:token="formData.vcodeToken" />
    </NFormItemRow>
    <NFormItemRow path="emailVCode" label="邮件验证码">
      <NInput v-show="emailSent" ref="inputEmailVCode" v-model:value="formData.emailVCode" />
      <div :class="emailSent ? '' : 'w-full'">
        <NButton block secondary :loading="loading" :disabled="sendButtonDisabled" @click="handleClickSendEmail">
          {{ sendButtonTitle }}
        </NButton>
      </div>
    </NFormItemRow>
    <NButton v-show="emailSent" type="primary" block strong :loading="loading" @click="handleClickRegister">
      注册
    </NButton>
  </NForm>
</template>
