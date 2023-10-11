<template>
  <n-modal v-model:show="modalVisible" preset="card" :title="title" class="w-500px max-w-full">
    <n-form ref="formRef" label-placement="top" :model="formModel" :rules="rules">
      <n-grid cols="1" :x-gap="18" item-responsive responsive="screen">
        <n-form-item-grid-item label="旧密码" path="name">
          <n-input v-model:value="formModel.oldPassword" type="password" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="新密码" path="amount">
          <n-input v-model:value="formModel.newPassword" type="password" />
        </n-form-item-grid-item>
      </n-grid>
      <n-space class="w-full pt-16px" :size="24" justify="end">
        <n-button class="w-72px" @click="closeModal">取消</n-button>
        <n-button class="w-72px" type="primary" @click="handleSubmit">确定</n-button>
      </n-space>
    </n-form>
  </n-modal>
</template>

<script setup lang="ts">
import { ref, computed, reactive } from 'vue';
import type { FormInst, FormItemRule } from 'naive-ui';
import { createRequiredFormRule } from '@/utils';
import { changePassword } from '~/src/service';
import { hashPassword } from '~/src/utils/auth';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
}

defineOptions({ name: 'TableActionModal' });

const props = withDefaults(defineProps<Props>(), {});

interface Emits {
  (e: 'update:visible', visible: boolean): void;
}

const emit = defineEmits<Emits>();

const modalVisible = computed({
  get() {
    return props.visible;
  },
  set(visible) {
    emit('update:visible', visible);
  }
});

const title = '修改密码';

const formRef = ref<HTMLElement & FormInst>();

type FormModel = {
  oldPassword: string;
  newPassword: string;
};

const formModel = reactive<FormModel>(createDefaultFormModel());

const closeModal = () => {
  modalVisible.value = false;
  Object.assign(formModel, createDefaultFormModel());
};

const rules: Record<string, FormItemRule | FormItemRule[]> = {
  oldPassword: createRequiredFormRule('请输入旧密码'),
  newPassword: createRequiredFormRule('请输入新密码')
};

function createDefaultFormModel(): FormModel {
  return {
    oldPassword: '',
    newPassword: ''
  };
}

async function handleSubmit() {
  await formRef.value?.validate();
  const { data } = await changePassword(hashPassword(formModel.oldPassword), hashPassword(formModel.newPassword));
  if (data?.code === 0) {
    window.$message?.success('密码修改成功');
    closeModal();
  }
}
</script>

<style scoped></style>
