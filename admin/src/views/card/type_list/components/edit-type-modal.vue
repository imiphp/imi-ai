<template>
  <n-modal v-model:show="modalVisible" preset="card" :title="title" class="w-800px max-w-full">
    <n-form ref="formRef" label-placement="left" :label-width="150" :model="formModel" :rules="rules">
      <n-tabs type="line" animated class="h-full" pane-wrapper-class="h-full" pane-class="h-full">
        <n-tab-pane name="content" tab="基本信息">
          <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
            <n-form-item-grid-item label="名称" path="name">
              <n-input v-model:value="formModel.name" />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="初始余额" path="amount">
              <n-input-number v-model:value="formModel.amount" :precision="0" />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="有效期(秒)" path="expireSeconds">
              <n-input-number v-model:value="formModel.expireSeconds" :min="0" :precision="0" class="w-[120px]" />
              <span class="ml-2">0为永久</span>
            </n-form-item-grid-item>
            <n-form-item-grid-item label="每用户最多激活次数" path="memberActivationLimit">
              <n-input-number
                v-model:value="formModel.memberActivationLimit"
                :min="0"
                :precision="0"
                class="w-[120px]"
              />
              <span class="ml-2">0为不限制</span>
            </n-form-item-grid-item>
            <n-form-item-grid-item label="状态" path="enable">
              <n-switch v-model:value="formModel.enable">
                <template #checked>启用</template>
                <template #unchecked>禁用</template>
              </n-switch>
            </n-form-item-grid-item>
          </n-grid>
        </n-tab-pane>
        <n-tab-pane name="vector" tab="销售">
          <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
            <n-form-item-grid-item label="状态" path="saleEnable">
              <n-switch v-model:value="formModel.saleEnable">
                <template #checked>启用</template>
                <template #unchecked>禁用</template>
              </n-switch>
            </n-form-item-grid-item>
            <n-form-item-grid-item label="原价" path="salePrice">
              <n-input-number v-model:value="formModel.salePrice" :precision="2" />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="实际售价" path="saleActualPrice">
              <n-input-number v-model:value="formModel.saleActualPrice" :precision="2" />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="VIP余额" path="salePrice">
              <n-switch v-model:value="formModel.salePaying" />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="销售排序" path="saleIndex">
              <n-input-number v-model:value="formModel.saleIndex" :precision="0" :min="0" :max="255" />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="销售开始时间" path="saleBeginTime">
              <n-date-picker v-model:value="formModel.saleBeginTime" type="datetime" clearable />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="销售结束时间" path="saleEndTime">
              <n-date-picker v-model:value="formModel.saleEndTime" type="datetime" clearable />
            </n-form-item-grid-item>
            <n-form-item-grid-item label="销售数量限制" path="saleLimitQuantity">
              <n-input-number v-model:value="formModel.saleLimitQuantity" :min="0" :precision="0" class="w-[120px]" />
              <span class="ml-2">0为不限制</span>
            </n-form-item-grid-item>
          </n-grid>
        </n-tab-pane>
      </n-tabs>
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
import { createRequiredFormRule, timespanHuman } from '@/utils';
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

type FormModel = Pick<
  Card.CardType,
  | 'name'
  | 'amount'
  | 'expireSeconds'
  | 'enable'
  | 'memberActivationLimit'
  | 'salePrice'
  | 'saleActualPrice'
  | 'saleEnable'
  | 'saleIndex'
  | 'saleLimitQuantity'
  | 'salePaying'
> & {
  saleBeginTime: number | undefined;
  saleEndTime: number | undefined;
};

const formModel = reactive<FormModel>(createDefaultFormModel());

const rules: Record<string, FormItemRule | FormItemRule[]> = {
  name: createRequiredFormRule('请输入名称'),
  amount: createRequiredFormRule('请输入初始余额'),
  expireSeconds: createRequiredFormRule('请输入有效期'),
  memberActivationLimit: createRequiredFormRule('请输入每个用户最多激活次数')
};

function createDefaultFormModel(): FormModel {
  return {
    name: '',
    amount: 0,
    expireSeconds: 0,
    enable: true,
    memberActivationLimit: 0,
    salePrice: 0,
    saleActualPrice: 0,
    saleEnable: false,
    saleIndex: 127,
    saleBeginTime: undefined,
    saleEndTime: undefined,
    saleLimitQuantity: 0,
    salePaying: false
  };
}

function handleUpdateFormModel(model: Partial<FormModel>) {
  const data = { ...createDefaultFormModel(), ...model };
  data.saleBeginTime = data.saleBeginTime ? data.saleBeginTime * 1000 : undefined;
  data.saleEndTime = data.saleEndTime ? data.saleEndTime * 1000 : undefined;
  data.salePrice = parseFloat((data.salePrice / 100).toFixed(2));
  data.saleActualPrice = parseFloat((data.saleActualPrice / 100).toFixed(2));
  Object.assign(formModel, data);
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
  const formModelData = { ...formModel };
  formModelData.saleBeginTime = formModelData.saleBeginTime ? formModelData.saleBeginTime / 1000 : 0;
  formModelData.saleEndTime = formModelData.saleEndTime ? formModelData.saleEndTime / 1000 : 0;
  formModelData.salePrice *= 100;
  formModelData.saleActualPrice *= 100;
  let response;
  if (props.type === 'add') {
    response = await createCardType(
      formModelData.name,
      formModelData.amount,
      formModelData.expireSeconds,
      formModelData.enable,
      formModelData.memberActivationLimit,
      formModelData.salePrice,
      formModelData.saleActualPrice,
      formModelData.saleEnable,
      formModelData.saleIndex,
      formModelData.saleBeginTime,
      formModelData.saleEndTime,
      formModelData.saleLimitQuantity
    );
  } else if (props.editData) {
    response = await updateCardType(props.editData.id, formModelData);
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
