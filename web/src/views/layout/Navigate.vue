<script setup lang='tsx'>
import type { VNode } from 'vue'
import { computed, h, ref, watch } from 'vue'

import type { FormInst, FormItemRule } from 'naive-ui'
import { NBadge, NButton, NDropdown, NForm, NFormItem, NIcon, NInput, NMenu, NModal, NSpin, useMessage } from 'naive-ui'

import { RouterLink, useRouter } from 'vue-router'
import { Gift, LockClosedOutline, LogInOutline, LogOutOutline, Menu, Person, PersonAddOutline, WalletOutline } from '@vicons/ionicons5'
import { Invitation, MemberAvatar } from './components'
import logo from '@/assets/logo.png'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { useAuthStore, useUserStore } from '@/store'
import type { UserInfo } from '@/store/modules/user/helper'
import { cardInfo, changePassword, config, updateProfile } from '@/api'
import { hashPassword } from '@/utils/functions'

const { isMobile } = useBasicLayout()
const authStore = useAuthStore()
const userStore = useUserStore()
const router = useRouter()
const message = useMessage()
const balance = ref('0')
const payingBalance = ref('0')
const showInvitationMenu = ref(true)

const menuOptions = [
  {
    label: () =>
      h(
        RouterLink,
        {
          to: {
            name: 'Home',
          },
        },
        { default: () => '首页' },
      ),
    key: 'Home',
  },
  {
    label: () =>
      h(
        RouterLink,
        {
          to: {
            name: 'Chat',
          },
        },
        { default: () => 'AI聊天' },
      ),
    key: 'Chat',
  },
  {
    label: () =>
      h(
        RouterLink,
        {
          to: {
            name: 'AITool',
          },
        },
        { default: () => 'AI工具' },
      ),
    key: 'AITool',
  },
  {
    label: () =>
      h(
        RouterLink,
        {
          to: {
            name: 'PromptStore',
          },
        },
        { default: () => '模型市场' },
      ),
    key: 'PromptStore',
  },
  {
    label: () =>
      h(NBadge, { value: '热', offset: [6, 0], style: 'color: inherit' }, {
        default: () => h(
          RouterLink,
          {
            to: {
              name: 'Embedding',
            },
            // style: 'color: rgb(51, 54, 57)',
          },
          { default: () => '模型训练' },
        ),
      }),
    key: 'Embedding',
  },
  {
    label: () =>
      h(
        RouterLink,
        {
          to: {
            name: 'Tokenizer',
          },
        },
        { default: () => 'Token 分词器' },
      ),
    key: 'Tokenizer',
  },
  {
    label: () =>
      h(
        'a',
        {
          href: 'https://github.com/imiphp/imi-ai',
          target: '_blank',
        },
        '源码下载',
      ),
    key: 'Source',
  },
]

const logined = ref(false)

// 修改资料
const showUpdateProfile = ref(false)
const updateProfileLoading = ref(false)
const updateProfileData = ref<any>({})
async function handleUpdateProfile() {
  try {
    updateProfileLoading.value = true
    const response = await updateProfile(updateProfileData.value)
    userStore.updateUserInfo(response.data)
    showUpdateProfile.value = false
    message.success('修改资料成功')
  }
  finally {
    updateProfileLoading.value = false
  }
}

// 修改密码
const showChangePassword = ref(false)
const changePasswordLoading = ref(false)
const changePasswordInitData = {
  oldPassword: '',
  newPassword: '',
}
const changePasswordData = ref<any>(changePasswordInitData)
const changePasswordRules = {
  oldPassword: {
    required: true,
    message: '请输入旧密码',
  },
  newPassword: {
    required: true,
    message: '请输入新密码',
  },
  confirmPassword: [
    {
      required: true,
      message: '请再次输入密码',
      trigger: ['blur', 'password-input'],
    },
    {
      validator: (rule: FormItemRule, value: string) => value === changePasswordData.value.newPassword,
      message: '两次密码输入不一致',
      trigger: ['blur', 'password-input'],
    },
  ],
}
const changePasswordForm = ref<FormInst | null>(null)
async function handleChangePassword() {
  changePasswordForm.value?.validate().then(async () => {
    try {
      changePasswordLoading.value = true
      const response = await changePassword(hashPassword(changePasswordData.value.oldPassword), hashPassword(changePasswordData.value.newPassword))
      userStore.updateUserInfo(response.data)
      showChangePassword.value = false
      message.success('密码修改成功')
    }
    finally {
      changePasswordLoading.value = false
    }
  })
}

// 邀请奖励
const showInvitation = ref(false)

const rightMenuOptions = ref([
  {
    label: () => [h(MemberAvatar, { style: 'display:inline-block; vertical-align: middle; margin-right: 0.5em' }), isMobile.value ? undefined : userStore.userInfo.nickname],
    key: 'Member',
    children: [
      {
        label: () => h('a', {
          onclick: () => {
            updateProfileData.value = {
              nickname: userStore.userInfo.nickname,
            }
            showUpdateProfile.value = true
          },
        }, '修改资料'),
        key: 'Nickname',
        show: computed(() => logined.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(Person) }),
      },
      {
        label: () =>
          h(
            RouterLink,
            {
              to: {
                name: 'Card',
              },
            },
            { default: () => `我的卡包 (${balance.value})` },
          ),
        key: 'Card',
        show: computed(() => logined.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(WalletOutline) }),
      },
      {
        label: () =>
          h(
            RouterLink,
            {
              to: {
                name: 'Card',
              },
            },
            { default: () => `VIP余额 (${payingBalance.value})` },
          ),
        key: 'PayingCard',
        show: computed(() => logined.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(WalletOutline) }),
      },
      {
        label: () =>
          h(
            RouterLink,
            {
              to: {
                name: 'Login',
              },
            },
            { default: () => '登录' },
          ),
        key: 'Login',
        show: computed(() => !logined.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(LogInOutline) }),
      },
      {
        label: () =>
          h(
            RouterLink,
            {
              to: {
                name: 'Register',
              },
            },
            { default: () => '注册' },
          ),
        key: 'Register',
        show: computed(() => !logined.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(PersonAddOutline) }),
      },
      {
        label: () => h('a', {
          onclick: () => {
            showInvitation.value = true
          },
        }, '邀请奖励'),
        key: 'Invitation',
        show: computed(() => logined.value && showInvitationMenu.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(Gift) }),
      },
      {
        label: () => h('a', {
          onclick: () => {
            changePasswordData.value = changePasswordInitData
            showChangePassword.value = true
          },
        }, '修改密码'),
        key: 'ChangePassword',
        show: computed(() => logined.value),
        icon: (): VNode => h(NIcon, null, { default: () => h(LockClosedOutline) }),
      },
      {
        label: '退出',
        key: 'Logout',
        show: logined,
        icon: (): VNode => h(NIcon, null, { default: () => h(LogOutOutline) }),
      },
    ],
  },
])

const selectedKey = ref(router.currentRoute.value.name?.toString() || 'Home')
const rightMenuSelectedKey = ref('')

const showDropdownMenu = ref(false)

watch(
  () => router.currentRoute.value,
  (newValue: any) => {
    if (newValue.path.startsWith('/embedding'))
      selectedKey.value = 'Embedding'
    else
      selectedKey.value = newValue.name?.toString() || 'Home'
  },
  { immediate: true },
)

watch(
  () => rightMenuSelectedKey.value,
  (newValue: string) => {
    switch (newValue) {
      case 'Logout':
        authStore.removeToken()
        userStore.resetUserInfo()
        message.success('退出成功')
        router.replace('/')
        break
    }
    rightMenuSelectedKey.value = ''
  },
)

watch(() => userStore.userInfo, (newValue: UserInfo) => {
  logined.value = (undefined !== newValue.recordId && newValue.recordId.length > 0)
},
{ immediate: true })

let promises: any = null

async function onMouseEnter() {
  if (!logined.value)
    return
  if (promises)
    return

  promises = Promise.all([
    (async () => {
      try {
        const response = await config()
        showInvitationMenu.value = response.data['config:member'].config.enableInvitation
      }
      catch (e) {
        window.console.log(e)
      }
    })(),
    (async () => {
      try {
        const response = await cardInfo(() => {})
        balance.value = response.balanceText
        payingBalance.value = response.payingBalanceText
      }
      catch (e) {
        balance.value = '加载失败'
        payingBalance.value = '加载失败'
        window.console.log(e)
      }
    })(),
  ])
  await promises
  promises = null
}
</script>

<template>
  <div class="border-b border-[#efeff5]">
    <div class="navigate wrap flex justify-between">
      <div v-if="isMobile">
        <NDropdown v-model:show="showDropdownMenu" trigger="click" :options="menuOptions" size="huge">
          <button type="button" class="h-full">
            <NIcon size="30" :class="`align-middle ${showDropdownMenu ? 'text-black' : 'text-gray-500'}`" :component="Menu" />
          </button>
        </NDropdown>
      </div>
      <div v-if="isMobile" />
      <div :class="isMobile ? 'left-0 right-0 self-center absolute w-full block !z-0' : 'flex'">
        <RouterLink to="/" class="logo">
          <img :src="logo" alt="logo" class="box-border mr-2" :class="[isMobile ? 'mobile' : '']">
          <h2 class="title">
            imi AI
          </h2>
        </RouterLink>
        <NMenu v-if="!isMobile" v-model:value="selectedKey" class="header-menu ml-10 !max-w-fit" mode="horizontal" :options="menuOptions" />
      </div>
      <NMenu v-model:value="rightMenuSelectedKey" class="member-menu !max-w-fit" mode="horizontal" :options="rightMenuOptions" dropdown-placement="bottom-end" @mouseenter="onMouseEnter" />
    </div>
  </div>

  <!-- 修改资料 -->
  <NModal
    v-if="showUpdateProfile"
    v-model:show="showUpdateProfile"
    :mask-closable="!changePasswordLoading"
    :close-on-esc="!updateProfileLoading"
    :closable="!updateProfileLoading"
    preset="card"
    title="修改资料"
    style="width: 95%; max-width: 500px"
  >
    <NSpin :show="updateProfileLoading">
      <NForm
        :model="updateProfileData"
        label-placement="left"
        label-width="auto"
        require-mark-placement="right-hanging"
      >
        <NFormItem label="昵称">
          <NInput v-model:value="updateProfileData.nickname" />
        </NFormItem>
        <div style="display: flex; justify-content: flex-end">
          <NButton round type="primary" @click="handleUpdateProfile">
            保存
          </NButton>
        </div>
      </NForm>
    </NSpin>
  </NModal>

  <!-- 修改密码 -->
  <NModal
    v-if="showChangePassword"
    v-model:show="showChangePassword"
    :mask-closable="!changePasswordLoading"
    :close-on-esc="!changePasswordLoading"
    :closable="!changePasswordLoading"
    preset="card"
    title="修改密码"
    style="width: 95%; max-width: 500px"
  >
    <NSpin :show="changePasswordLoading">
      <NForm
        ref="changePasswordForm"
        :model="changePasswordData"
        label-placement="left"
        label-width="auto"
        require-mark-placement="right-hanging"
        :rules="changePasswordRules"
      >
        <NFormItem path="oldPassword" label="旧密码">
          <NInput v-model:value="changePasswordData.oldPassword" type="password" />
        </NFormItem>
        <NFormItem path="newPassword" label="新密码">
          <NInput v-model:value="changePasswordData.newPassword" type="password" />
        </NFormItem>
        <NFormItem path="confirmPassword" label="确认密码">
          <NInput v-model:value="changePasswordData.confirmPassword" type="password" />
        </NFormItem>
        <div style="display: flex; justify-content: flex-end">
          <NButton round type="primary" @click="handleChangePassword">
            保存
          </NButton>
        </div>
      </NForm>
    </NSpin>
  </NModal>

  <!-- 邀请奖励 -->
  <NModal
    v-if="showInvitation"
    v-model:show="showInvitation"
    preset="card"
    title="邀请奖励"
    style="width: 95%; max-width: 640px"
  >
    <Invitation />
  </NModal>
</template>

<style lang="less" scoped>
  .navigate {
    >*{
      z-index: 10
    }
  }
  .logo {
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    white-space: nowrap;

    img {
      width: auto;
      height: 48px;
    }
    img.mobile {
      width: 48px;
      height: auto;
    }

    .title {
      margin: 0;
      font-size: 20px;
    }
  }
</style>

<style lang="less">
.header-menu {
  .n-menu-item-content .n-menu-item-content-header {
    overflow: inherit !important;
  }
}
.header-menu, .member-menu {
  .n-menu-item, .n-menu-item-content{
    height: 100% !important;
  }
}
.member-menu {
  .n-menu-item-content {
    padding: 0 !important;
  }
}
</style>
