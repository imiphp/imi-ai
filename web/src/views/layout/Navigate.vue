<script setup lang='ts'>
import type { Ref } from 'vue'
import { computed, h, ref, watch } from 'vue'

import type { MenuOption } from 'naive-ui'
import { NLayout, NLayoutContent, NLayoutHeader, NLayoutSider, NMenu, useMessage } from 'naive-ui'

import { RouterLink, useRouter } from 'vue-router'
import { MemberAvatar } from './components'
import logo from '@/assets/logo.png'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { useAuthStore, useUserStore } from '@/store'
import type { UserInfo } from '@/store/modules/user/helper'
import { cardInfo } from '@/api'

const { isMobile } = useBasicLayout()
const authStore = useAuthStore()
const userStore = useUserStore()
const router = useRouter()
const message = useMessage()

const balance = ref('0')

const menuOptions: MenuOption[] = [
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

const rightMenuOptions: Ref<any> = ref([
  {
    label: () => h(MemberAvatar),
    key: 'Member',
    children: [
      {
        label: () => userStore.userInfo.nickname,
        key: 'Nickname',
        show: logined,
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
        show: logined,
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
      },
      {
        label: '退出',
        key: 'Logout',
        show: logined,
      },
    ],
  },
])

const selectedKey = ref(router.currentRoute.value.name?.toString() || 'Home')
const rightMenuSelectedKey = ref('')

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
  const response = await cardInfo()
  balance.value = response.balanceText
}
</script>

<template>
  <NLayout>
    <NLayoutHeader :bordered="true">
      <NLayout class="wrap h-[48px] leading-[48px]" has-sider>
        <NLayoutSider collapse-mode="width" :collapsed="isMobile" :collapsed-width="50">
          <div class="logo">
            <img :src="logo" alt="logo" class="box-border" :class="[isMobile ? 'ml-[2px] mobile' : 'mr-2']">
            <h2 v-if="!isMobile" class="title">
              imi AI
            </h2>
          </div>
        </NLayoutSider>
        <NLayoutContent content-style="justify-content: space-between;display: flex;">
          <NMenu v-model:value="selectedKey" class="header-menu" mode="horizontal" :options="menuOptions" />
          <NMenu v-model:value="rightMenuSelectedKey" class="header-menu" mode="horizontal" :options="rightMenuOptions" @mouseenter="onMouseEnter" />
        </NLayoutContent>
      </nlayout>
    </NLayoutHeader>
  </NLayout>
</template>

<style lang="less" scoped>
  .logo {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 48px;
    line-height: 48px;
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
</style>
