<template>
  <n-modal v-model:show="modalVisible" preset="card" :title="title" class="w-700px">
    <n-form ref="formRef" label-placement="left" :label-width="80" :model="formModel" :rules="rules">
      <n-grid :cols="24" :x-gap="18">
        <n-form-item-grid-item :span="12" label="用户名" path="account">
          <n-input v-model:value="formModel.account" />
        </n-form-item-grid-item>
        <n-form-item-grid-item :span="12" label="昵称" path="nickname">
          <n-input v-model:value="formModel.nickname" />
        </n-form-item-grid-item>
        <n-form-item-grid-item :span="12" label="密码" path="password">
          <n-input v-model:value="formModel.password" />
        </n-form-item-grid-item>
        <n-form-item-grid-item :span="12" label="状态" path="password">
          <n-select
            v-model:value="formModel.status"
            class="!w-[140px]"
            :options="enums.AdminMemberStatus"
            label-field="text"
            value-field="value"
          />
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
import { createAdminMember, updateAdminMember } from '~/src/service';

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
  editData?: UserManagement.User | null;
  enums: any;
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
    add: '新建用户',
    edit: '编辑用户'
  };
  return titles[props.type];
});

const formRef = ref<HTMLElement & FormInst>();

type FormModel = Pick<UserManagement.User, 'account' | 'nickname' | 'status'> & { password: string };

const formModel = reactive<FormModel>(createDefaultFormModel());

const constRules: Record<string, FormItemRule | FormItemRule[]> = {
  account: createRequiredFormRule('请输入用户名'),
  nickname: createRequiredFormRule('请输入昵称')
};
const rules = computed(() => {
  const result = { ...constRules };
  if (props.type === 'add') {
    result.password = createRequiredFormRule('请输入密码');
  }
  return result;
});

function createDefaultFormModel(): FormModel {
  return {
    account: '',
    nickname: '',
    password: '',
    status: 1
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
    response = await createAdminMember(formModel.account, formModel.nickname, formModel.password, formModel.status);
  } else if (props.editData) {
    response = await updateAdminMember(
      props.editData.id,
      formModel.account,
      formModel.nickname,
      formModel.password,
      formModel.status
    );
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
