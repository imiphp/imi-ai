<script lang="ts" setup>
import { NAvatar } from 'naive-ui'
import { computed } from 'vue'
import { useUserStore } from '@/store'
import defaultAvatar from '@/assets/avatar.jpg'

const userStore = useUserStore()

const userInfo = computed(() => userStore.userInfo)
</script>

<template>
  <NAvatar
    round
    :size="36"
    :src="userInfo.avatar ?? defaultAvatar"
    :fallback-src="defaultAvatar"
    style="display: block;"
  />
</template>
