<script lang="ts" setup>
import type { InputInst } from 'naive-ui'
import { NButton, NCard, NFormItemRow, NIcon, NInput, NP, NPopover, NSpace, useMessage } from 'naive-ui'

import { computed, onMounted, ref } from 'vue'
import { CheckmarkCircleOutline } from '@vicons/ionicons5'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/store'
import { copyToClip } from '@/utils/copy'
import { bindInvitationCode, config } from '@/api'
import { useBasicLayout } from '@/hooks/useBasicLayout'

const userStore = useUserStore()
const message = useMessage()
const router = useRouter()
const { isMobile } = useBasicLayout()

const invitationUrl = computed(() => {
  return location.origin + location.pathname + router.resolve({
    name: 'Register',
    params: {
      invitationCode: userStore.userInfo.invitationCode,
    },
  }).href
})
const invitationCodeInput = ref<InputInst>()
const invitationUrlInput = ref<InputInst>()
const showCodePopover = ref(false)
const showUrlPopover = ref(false)
const memberConfig = ref<any>({})
const invitationCode = ref('')
const loading = ref(false)

async function onInvitationCodeClick() {
  invitationCodeInput.value?.select()
  await copyToClip(userStore.userInfo.invitationCode)
  invitationCodeInput.value?.select()
}

async function onInvitationUrlClick() {
  invitationUrlInput.value?.select()
  await copyToClip(userStore.userInfo.invitationCode)
  invitationUrlInput.value?.select()
}

function onShowCodeCopySuccess(value: boolean) {
  if (value) {
    showCodePopover.value = value
    setTimeout(() => {
      showCodePopover.value = false
    }, 1500)
  }
}

function onShowUrlCopySuccess(value: boolean) {
  if (value) {
    showUrlPopover.value = value
    setTimeout(() => {
      showUrlPopover.value = false
    }, 1500)
  }
}

async function loadConfig() {
  const response = await config()
  memberConfig.value = response.data['config:member'].config
}

async function handleBindInvitationCode() {
  try {
    loading.value = true
    await bindInvitationCode(invitationCode.value)
    message.success('激活成功')
    await userStore.updateUserInfoFromApi()
  }
  finally {
    loading.value = false
  }
}

onMounted(async () => {
  await loadConfig()
})
</script>

<template>
  <NCard>
    <NFormItemRow label="邀请码：" label-placement="left" label-width="6em">
      <NPopover v-model:show="showCodePopover" trigger="click" :show-arrow="false" :on-update:show="onShowCodeCopySuccess">
        <template #trigger>
          <NInput ref="invitationCodeInput" class="!w-[12em] text-center" :value="userStore.userInfo.invitationCode" readonly @click="onInvitationCodeClick" />
        </template>
        <NIcon size="24" class="align-middle text-green-600" :component="CheckmarkCircleOutline" />
        <span>复制成功</span>
      </NPopover>
    </NFormItemRow>
    <NFormItemRow label="邀请链接：" :label-placement="isMobile ? 'top' : 'left'" label-width="6em">
      <NPopover v-model:show="showUrlPopover" trigger="click" :show-arrow="false" :on-update:show="onShowUrlCopySuccess">
        <template #trigger>
          <NInput ref="invitationUrlInput" :value="invitationUrl" readonly @click="onInvitationUrlClick" />
        </template>
        <NIcon size="24" class="align-middle text-green-600" :component="CheckmarkCircleOutline" />
        <span>复制成功</span>
      </NPopover>
    </NFormItemRow>
    <NP class="!mt-0">
      每邀请一位新用户，你可获得 <span class="text-[#f0a020] font-bold">{{ memberConfig.inviterGiftAmount }}</span> 卡包余额。
    </NP>
    <NP>被邀请用户可获得 <span class="text-[#f0a020] font-bold">{{ memberConfig.inviteeGiftAmount }}</span> 卡包余额。</NP>
  </NCard>
  <NCard v-if="memberConfig.enableInputInvitation" title="填写邀请码" class="mt-4">
    <template v-if="userStore.userInfo.inviterId > 0">
      <NP>你已激活过邀请码！</NP>
    </template>
    <template v-else>
      <NSpace vertical>
        <NInput v-model:value="invitationCode" class="!text-3xl text-center p-1" size="large" placeholder="请输入邀请码" />
        <NP>激活邀请码后你可获得 <span class="text-[#f0a020] font-bold">{{ memberConfig.inviteeGiftAmount }}</span> 卡包余额。</NP>
        <div class="text-center">
          <NButton type="primary" size="large" :loading="loading" @click="handleBindInvitationCode">
            激 活
          </NButton>
        </div>
      </NSpace>
    </template>
  </NCard>
</template>
