<script setup lang='ts'>
import { h, ref, watch } from 'vue'
import type { MenuOption } from 'naive-ui'
import { NLayout, NLayoutContent, NLayoutHeader, NLayoutSider, NMenu } from 'naive-ui'
import { RouterLink } from 'vue-router'
import logo from '@/assets/logo.png'
import { router } from '@/router'
import { useBasicLayout } from '@/hooks/useBasicLayout'

const { isMobile } = useBasicLayout()

const menuOptions: MenuOption[] = [
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
]

const selectedKey = ref(router.currentRoute.value.name?.toString() || 'Root')

watch(
  () => router.currentRoute.value,
  (newValue: any) => {
    if (newValue.path.startsWith('/embedding'))
      selectedKey.value = 'Embedding'
    else
      selectedKey.value = newValue.name?.toString() || 'Root'
  },
  { immediate: true },
)
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
        <NLayoutContent>
          <NMenu v-model:value="selectedKey" mode="horizontal" :options="menuOptions" />
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
