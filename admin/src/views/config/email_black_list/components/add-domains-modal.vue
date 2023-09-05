<template>
  <n-modal v-model:show="modalVisible" preset="card" title="批量增加邮箱域名黑名单" class="w-500px max-w-full">
    <n-form ref="formRef" label-placement="left" :label-width="80" :model="formModel" :rules="rules">
      <n-grid cols="1" :x-gap="18" item-responsive responsive="screen">
        <n-form-item-grid-item label="域名">
          <n-input v-model:value="formModel.domains" type="textarea" rows="10" placeholder="一行一个" />
        </n-form-item-grid-item>
      </n-grid>
    </n-form>
    <n-space class="w-full pt-16px" :size="24" justify="end">
      <n-button class="w-72px" @click="closeModal">取消</n-button>
      <n-button class="w-72px" type="primary" @click="handleSubmit">确定</n-button>
    </n-space>
  </n-modal>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import type { FormInst, FormItemRule } from 'naive-ui';
import { createRequiredFormRule } from '~/src/utils';
import { addEmailBlackList } from '~/src/service';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
}

const props = defineProps<Props>();

interface Emits {
  (e: 'update:visible', visible: boolean): void;
}

const emit = defineEmits<Emits>();

const formRef = ref<HTMLElement & FormInst>();

const modalVisible = computed({
  get() {
    return props.visible;
  },
  set(visible) {
    emit('update:visible', visible);
  }
});
const closeModal = () => {
  modalVisible.value = false;
};

const rules: Record<string, FormItemRule | FormItemRule[]> = {
  domains: createRequiredFormRule('请输入邮箱地址')
};

const formModel = ref({
  domains: ''
});

const showResult = ref(false);

async function handleSubmit() {
  if (showResult.value) {
    closeModal();
    return;
  }
  await formRef.value?.validate();

  const domains = formModel.value.domains
    .split(/[\r\n]/)
    .map(item => item.trim())
    .filter(item => item.length > 0);

  const { data } = await addEmailBlackList(domains);
  if (data?.code === 0) {
    window.$message?.info('增加成功');
    closeModal();
  }
}
</script>
