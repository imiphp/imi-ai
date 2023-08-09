<script setup lang='ts'>
import type { VNode } from 'vue'
import { computed, h, ref, watch } from 'vue'

import { NButton, NDropdown, NForm, NFormItem, NIcon, NInput, NMenu, NModal, NSpin, useMessage } from 'naive-ui'

import { RouterLink, useRouter } from 'vue-router'
import { LogInOutline, LogOutOutline, Menu, Person, PersonAddOutline, WalletOutline } from '@vicons/ionicons5'
import { MemberAvatar } from './components'
import logo from '@/assets/logo.png'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { useAuthStore, useUserStore } from '@/store'
import type { UserInfo } from '@/store/modules/user/helper'
import { cardInfo, updateProfile } from '@/api'

const { isMobile } = useBasicLayout()
const authStore = useAuthStore()
const userStore = useUserStore()
const router = useRouter()
const message = useMessage()

const balance = ref('0')

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
            name: 'Embedding',
          },
        },
        { default: () => '模型训练' },
      ),
    key: 'Embedding',
  },
  // {
  //   label: () =>
  //     h(
  //       'a',
  //       {
  //         href: 'https://github.com/imiphp/imi-ai',
  //         target: '_blank',
  //       },
  //       '源码下载',
  //     ),
  //   key: 'Source',
  // },
]

const logined = ref(false)

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

async function onMouseEnter() {
  if (!logined.value)
    return
  const response = await cardInfo(() => {})
  balance.value = response.balanceText
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
        <NMenu v-if="!isMobile" v-model:value="selectedKey" class="header-menu ml-10" mode="horizontal" :options="menuOptions" />
      </div>
      <NMenu v-model:value="rightMenuSelectedKey" class="header-menu member-menu" mode="horizontal" :options="rightMenuOptions" dropdown-placement="bottom-end" @mouseenter="onMouseEnter" />
    </div>
  </div>

  <NModal
    v-model:show="showUpdateProfile"
    :mask-closable="false"
    :close-on-esc="!updateProfileLoading"
    :closable="!updateProfileLoading"
    preset="card"
    title="修改资料"
    style="width: 95%; max-width: 500px"
  >
    <NSpin :show="updateProfileLoading">
      <NForm
        ref="formRef"
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
