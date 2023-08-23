<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { NSpin } from 'naive-ui';
import { fetchVcode } from '~/src/service';

interface Props {
  token: string;
}

interface Emit {
  (e: 'update:token', token: string): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

const src = ref('');
const token = computed({
  get: () => props.token,
  set: (value: string) => emit('update:token', value)
});
const loading = ref(false);

async function loadVCode() {
  try {
    loading.value = true;
    const { data } = await fetchVcode();
    if (data) {
      src.value = `data:image/jpg;base64,${data.image}`;
      token.value = data.token;
    } else {
      src.value = '';
      token.value = '';
    }
  } finally {
    loading.value = false;
  }
}

defineExpose({
  loadVCode
});

onMounted(async () => {
  await loadVCode();
});
</script>

<template>
  <n-spin :show="loading" class="h-full vcode-spin">
    <img :src="src" class="h-full w-auto max-w-max cursor-pointer" @click="loadVCode" />
  </n-spin>
</template>

<style lang="less">
.vcode-spin .n-spin-content {
  height: 100%;
}
</style>
