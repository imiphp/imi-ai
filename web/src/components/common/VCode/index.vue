<script setup lang='tsx'>
import { computed, onMounted, ref } from 'vue'
import { NSpin } from 'naive-ui'
import { vcode } from '@/api'

interface Props {
  token: string
}

interface Emit {
  (e: 'update:token', token: string): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const src = ref('')
const token = computed({
  get: () => props.token,
  set: (token: string) => emit('update:token', token),
})
const loading = ref(false)

async function loadVCode() {
  try {
    loading.value = true
    const response = await vcode()
    src.value = `data:image/jpg;base64,${response.image}`
    token.value = response.token
  }
  finally {
    loading.value = false
  }
}

defineExpose({
  loadVCode,
})

onMounted(async () => {
  await loadVCode()
})
</script>

<template>
  <NSpin :show="loading" class="h-full vcode-spin">
    <img :src="src" class="h-full w-auto max-w-max cursor-pointer" @click="loadVCode">
  </NSpin>
</template>

<style lang="less">
.vcode-spin .n-spin-content {
  height: 100%;
}
</style>
