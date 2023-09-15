<template>
  <n-modal v-model:show="modalVisible" preset="card" title="批量生成卡" class="w-500px max-w-full">
    <template v-if="showResult">
      <n-input type="textarea" :value="result" rows="10"></n-input>
    </template>
    <template v-else>
      <n-form ref="formRef" label-placement="left" :label-width="80" :model="formModel" :rules="rules">
        <n-grid cols="1" :x-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="生成数量" path="count">
            <n-input-number v-model:value="formModel.count" :precision="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="备注">
            <n-input v-model:value="formModel.remark" type="textarea" rows="3" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="付费标志">
            <n-switch v-model:value="formModel.paying" />
          </n-form-item-grid-item>
        </n-grid>
      </n-form>
    </template>
    <n-space class="w-full pt-16px" :size="24" justify="end">
      <n-button class="w-72px" @click="closeModal">取消</n-button>
      <n-button class="w-72px" type="primary" @click="handleSubmit">确定</n-button>
    </n-space>
  </n-modal>
</template>

<script setup lang="ts">
import { computed, nextTick, ref, watch } from 'vue';
import type { FormInst, FormItemRule } from 'naive-ui';
import { createRequiredFormRule } from '~/src/utils';
import { generateCard } from '~/src/service';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
  cardType: number;
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
  count: createRequiredFormRule('请输入数量')
};

const formModelDefault = {
  count: 1,
  remark: '',
  paying: false
};

const formModel = ref(formModelDefault);

const showResult = ref(false);
const result = ref('');

async function handleSubmit() {
  if (showResult.value) {
    closeModal();
    return;
  }
  await formRef.value?.validate();
  const { data } = await generateCard(
    props.cardType,
    formModel.value.count,
    formModel.value.remark,
    formModel.value.paying
  );
  if (data?.code === 0) {
    nextTick(() => {
      result.value = data.list.join('\r\n');
      showResult.value = true;
    });
    window.$message?.info('生成成功');
  }
}

watch(
  () => props.visible,
  newValue => {
    if (newValue) {
      showResult.value = false;
      formModel.value = formModelDefault;
    }
  }
);
</script>
