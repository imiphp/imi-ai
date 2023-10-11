<template>
  <n-dropdown :options="options" @select="handleDropdown">
    <hover-container class="px-12px" :inverted="theme.header.inverted">
      <icon-local-avatar class="text-32px" />
      <span class="pl-8px text-16px font-medium">{{ auth.userInfo.nickname }}</span>
    </hover-container>
  </n-dropdown>
  <change-password-modal v-model:visible="showChangePasswordModal" />
</template>

<script lang="ts" setup>
import { ref } from 'vue';
import type { DropdownOption } from 'naive-ui';
import { useAuthStore, useThemeStore } from '@/store';
import { useIconRender } from '@/composables';

defineOptions({ name: 'UserAvatar' });

const auth = useAuthStore();
const theme = useThemeStore();
const { iconRender } = useIconRender();
const showChangePasswordModal = ref(false);

const options: DropdownOption[] = [
  {
    label: '修改密码',
    key: 'changePassword',
    icon: iconRender({ icon: 'carbon:password' })
  },
  {
    type: 'divider',
    key: 'divider'
  },
  {
    label: '退出登录',
    key: 'logout',
    icon: iconRender({ icon: 'carbon:logout' })
  }
];

type DropdownKey = 'changePassword' | 'logout';

function handleDropdown(optionKey: string) {
  const key = optionKey as DropdownKey;
  if (key === 'changePassword') {
    showChangePasswordModal.value = true;
  } else if (key === 'logout') {
    window.$dialog?.info({
      title: '提示',
      content: '您确定要退出登录吗？',
      positiveText: '确定',
      negativeText: '取消',
      onPositiveClick: () => {
        auth.resetAuthStore();
      }
    });
  }
}
</script>

<style scoped></style>
