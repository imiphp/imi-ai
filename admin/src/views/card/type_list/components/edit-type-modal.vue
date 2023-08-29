<template>
  <n-modal v-model:show="modalVisible" preset="card" :title="title" class="w-700px max-w-full">
    <n-form ref="formRef" label-placement="left" :label-width="80" :model="formModel" :rules="rules">
      <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
        <n-form-item-grid-item label="名称" path="name">
          <n-input v-model:value="formModel.name" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="初始余额" path="amount">
          <n-input-number v-model:value="formModel.amount" :precision="0" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="有效期(秒)" path="expireSeconds">
          <n-input-number
            v-model:value="formModel.expireSeconds"
            :min="0"
            :precision="0"
            :disabled="expireSecondsDisable"
            @update:value="
              value => {
                if (formModel.expireSeconds == 0) {
                  expireSecondsDisable = true;
                }
              }
            "
          />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="永久">
          <n-checkbox
            v-model:checked="expireSecondsDisable"
            @update:checked="
              value => {
                if (expireSecondsDisable) {
                  formModel.expireSeconds = 0;
                }
              }
            "
          ></n-checkbox>
        </n-form-item-grid-item>
        <n-form-item-grid-item label="状态" path="password">
          <n-switch v-model:value="formModel.enable">
            <template #checked>启用</template>
            <template #unchecked>禁用</template>
          </n-switch>
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
import type { FormInst, FormItemRule } from 'naive-ui';
import { createRequiredFormRule } from '@/utils';
import { createCardType, updateCardType } from '~/src/service';

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
  editData?: Card.CardType | null;
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

const expireSecondsDisable = ref(false);

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
    add: '新建卡类型',
    edit: '编辑卡类型'
  };
  return titles[props.type];
});

const formRef = ref<HTMLElement & FormInst>();

type FormModel = Pick<Card.CardType, 'name' | 'amount' | 'expireSeconds' | 'enable'>;

const formModel = reactive<FormModel>(createDefaultFormModel());

const constRules: Record<string, FormItemRule | FormItemRule[]> = {
  account: createRequiredFormRule('请输入用户名'),
  nickname: createRequiredFormRule('请输入昵称')
};
const rules = computed(() => {
  const result = { ...constRules };
  return result;
});

function createDefaultFormModel(): FormModel {
  return {
    name: '',
    amount: 0,
    expireSeconds: 0,
    enable: true
  };
}

function handleUpdateFormModel(model: Partial<FormModel>) {
  Object.assign(formModel, { ...createDefaultFormModel(), ...model });
  expireSecondsDisable.value = formModel.expireSeconds === 0;
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
    response = await createCardType(formModel.name, formModel.amount, formModel.expireSeconds, formModel.enable);
  } else if (props.editData) {
    response = await updateCardType(props.editData.id, formModel);
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

<style scoped></style>
