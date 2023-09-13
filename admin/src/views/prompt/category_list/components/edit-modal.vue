<template>
  <n-modal
    v-model:show="modalVisible"
    preset="card"
    :title="title"
    class="w-[500px] max-w-full max-h-full edit-prompt-modal"
  >
    <div class="flex-col h-full">
      <n-form
        ref="formRef"
        label-placement="left"
        :label-width="60"
        :model="formModel"
        :rules="rules"
        class="flex-1 overflow-auto h-0"
      >
        <n-grid cols="1" :x-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="标题" path="type">
            <n-input v-model:value="formModel.title" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="排序">
            <n-input-number v-model:value="formModel.index" :min="0" :max="255" />
          </n-form-item-grid-item>
        </n-grid>
      </n-form>
      <n-space class="w-full pt-16px" :size="24" justify="end">
        <n-button class="w-72px" @click="closeModal">取消</n-button>
        <n-button class="w-72px" type="primary" @click="handleSubmit">确定</n-button>
      </n-space>
    </div>
  </n-modal>
</template>

<script setup lang="ts">
import { ref, computed, reactive, watch } from 'vue';
import type { FormInst, FormItemRule } from 'naive-ui';
import { createRequiredFormRule } from '@/utils';
import { createPromptCategory, updatePromptCategory } from '~/src/service';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
  /**
   * 弹窗类型
   * add: 新增
   * edit: 编辑
   */
  type?: 'add' | 'edit';
  /** 编辑的表格行数据 */
  editData?: FormModel | null;
}

export type ModalType = NonNullable<Props['type']>;

defineOptions({ name: 'TableActionModal' });

const props = withDefaults(defineProps<Props>(), {
  type: 'add',
  editData: null
});

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
const closeModal = () => {
  modalVisible.value = false;
};

const title = computed(() => {
  const titles: Record<ModalType, string> = {
    add: '新建提示语分类',
    edit: '编辑提示语分类'
  };
  return titles[props.type];
});

const formRef = ref<HTMLElement & FormInst>();

type FormModel = {
  id: number;
  title: string;
  index: number;
};

const formModel = reactive<FormModel>(createDefaultFormModel());

const rules: Record<string, FormItemRule | FormItemRule[]> = {
  title: createRequiredFormRule('请输入标题')
};

function createDefaultFormModel(): FormModel {
  return {
    id: 0,
    title: '',
    index: 128
  };
}

function handleUpdateFormModel(model: Partial<FormModel>) {
  Object.assign(formModel, { ...createDefaultFormModel(), ...model });
}

function handleUpdateFormModelByModalType() {
  const handlers: Record<ModalType, () => void> = {
    add: () => {
      const defaultFormModel = createDefaultFormModel();
      handleUpdateFormModel(defaultFormModel);
    },
    edit: () => {
      if (props.editData) {
        handleUpdateFormModel(props.editData);
      }
    }
  };

  handlers[props.type]();
}

async function handleSubmit() {
  await formRef.value?.validate();
  let response;
  if (props.type === 'add') {
    response = await createPromptCategory(formModel.title, formModel.index);
  } else if (props.editData) {
    response = await updatePromptCategory(props.editData.id, { ...(formModel as any) });
  } else {
    throw new Error('未知错误');
  }
  const { data } = response;
  if (data?.code === 0) closeModal();
}

watch(
  () => props.visible,
  newValue => {
    if (newValue) {
      handleUpdateFormModelByModalType();
    }
  }
);
</script>

<style lang="less">
.edit-prompt-modal {
  .n-card__content {
    height: 0;
  }
}
</style>
<style scoped></style>
