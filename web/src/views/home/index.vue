<script setup lang='ts'>
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { NButton, NResult } from 'naive-ui'

const router = useRouter()

const jumpCountDown = ref(0)
let jumpTimer: NodeJS.Timer | null = null

watch(router.currentRoute, () => {
  if (jumpTimer) {
    clearInterval(jumpTimer)
    jumpTimer = null
  }
})

function delayJump() {
  jumpCountDown.value = 3
  jumpTimer = setInterval(() => {
    if (--jumpCountDown.value === 0) {
      if (jumpTimer)
        clearInterval(jumpTimer)
      jump()
    }
  }, 1000)
}

function jump() {
  router.replace({ name: 'Chat' })
}

onMounted(() => {
  delayJump()
})
</script>

<template>
  <NResult status="info" title="首页还没做呢！" :description="`${jumpCountDown}秒后跳转到AI聊天...`">
    <template #footer>
      <NButton @click="jump">
        立即跳转
      </NButton>
    </template>
  </NResult>
</template>
