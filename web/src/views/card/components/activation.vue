<script setup lang='ts'>
import type { FormInst } from 'naive-ui'
import { NButton, NForm, NFormItemRow, NInput, useMessage } from 'naive-ui'

import type { Ref } from 'vue'
import { computed, ref } from 'vue'

import { VCode } from '@/components/common'
import { activation } from '@/api'

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

const message = useMessage()
const form = ref<FormInst | null>(null)
const formData = ref({
  cardId: '',
  vcode: '',
  vcodeToken: '',
})
const formRules = ref({
  cardId: {
    required: true,
    message: '请输入卡号',
  },
  vcode: {
    required: true,
    message: '请输入图形验证码',
  },
})
const loading = ref(false)
const vcode = ref<Ref | null>(null)
const inputVcode = ref<Ref | null>(null)

const buyCardText = import.meta.env.VITE_BUY_CARD_TEXT

async function handleActivation() {
  form.value?.validate().then(async () => {
    try {
      loading.value = true
      await activation(formData.value)
      message.success('激活成功')
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
</script>

<template>
  <div class="wrap !max-w-full !w-[500px]">
    <NForm ref="form" :model="formData" :rules="formRules">
      <NFormItemRow path="cardId" label="卡号">
        <NInput v-model:value="formData.cardId" placeholder="卡号" />
      </NFormItemRow>
      <NFormItemRow path="vcode" label="图形验证码">
        <NInput ref="inputVcode" v-model:value="formData.vcode" />
        <VCode ref="vcode" v-model:token="formData.vcodeToken" />
      </NFormItemRow>
      <NButton attr-type="submit" type="primary" block strong :loading="loading" @click="handleActivation">
        激活
      </NButton>
    </NForm>
    <p class="leading-8" v-text="buyCardText" />
  </div>
</template>
