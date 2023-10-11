<template>
  <n-modal v-model:show="modalVisible" preset="card" :title="title" class="w-800px max-w-full">
    <n-form ref="formRef" label-placement="left" :label-width="150" :model="formModel" :rules="rules">
      <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
        <n-form-item-grid-item label="熔断条件" path="condition">
          <n-input-number v-model:value="formModel.limitAmount" :min="0" />
          <n-select v-model:value="formModel.limitUnit" :options="rateLimitUnitOptions" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="熔断时长" path="breakDuration">
          <n-input-number v-model:value="formModel.breakDuration" :min="0">
            <template #suffix>秒</template>
          </n-input-number>
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
import { ref, computed, reactive, watch } from 'vue';
import type { FormInst, FormRules } from 'naive-ui';
import { createRequiredFormRule, rateLimitUnitOptions } from '@/utils';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
  /** 编辑的表格行数据 */
  editData?: any | null;
}

defineOptions({ name: 'TableActionModal' });

const props = withDefaults(defineProps<Props>(), {
  editData: null
});

interface Emits {
  (e: 'update:visible', visible: boolean): void;
  (e: 'update:editData', editData: any): void;
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

const editData = computed({
  get() {
    return props.editData;
  },
  set(value) {
    emit('update:editData', value);
  }
});

const closeModal = () => {
  modalVisible.value = false;
};

const title = '编辑熔断设置';

const formRef = ref<HTMLElement & FormInst>();

const formModel = reactive<any>(createDefaultFormModel());

const rules: FormRules = {
  breakDuration: createRequiredFormRule('请填写熔断时长')
};

function createDefaultFormModel(): any {
  return {
    limitUnit: 'second',
    limitAmount: 0,
    breakDuration: 0
  };
}

function handleUpdateFormModel() {
  Object.assign(formModel, { ...createDefaultFormModel(), ...props.editData });
}

async function handleSubmit() {
  await formRef.value?.validate();
  editData.value = { ...formModel };
  closeModal();
}

watch(
  () => props.visible,
  newValue => {
    if (newValue) {
      handleUpdateFormModel();
    }
  }
);
</script>

<style scoped></style>
