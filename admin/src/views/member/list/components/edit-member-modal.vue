<template>
  <n-modal v-model:show="modalVisible" preset="card" :title="title" class="w-700px max-w-full">
    <n-form ref="formRef" label-placement="left" :label-width="80" :model="formModel" :rules="rules">
      <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
        <n-form-item-grid-item label="昵称" path="nickname">
          <n-input v-model:value="formModel.nickname" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="邮箱" path="email">
          <n-input v-model:value="formModel.email" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="密码" path="password">
          <n-input v-model:value="formModel.password" />
        </n-form-item-grid-item>
        <n-form-item-grid-item label="状态" path="password">
          <n-select
            v-model:value="formModel.status"
            :options="enums.MemberStatus"
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
import { createMember, updateMember } from '~/src/service';
import { hashPassword } from '~/src/utils/auth';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
  /**
   * 弹窗类型
   * add: 新增
   * edit: 编辑
   */
  type: 'add' | 'edit';
  /** 编辑的表格行数据 */
  editData: Member.Member;
  enums: any;
}

export type ModalType = NonNullable<Props['type']>;

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
const closeModal = () => {
  modalVisible.value = false;
};

const formRef = ref<HTMLElement & FormInst>();

type FormModel = Pick<Member.Member, 'email' | 'nickname' | 'status'> & { password: string };

const formModel = reactive<FormModel>(createDefaultFormModel());

const constRules: Record<string, FormItemRule | FormItemRule[]> = {
  email: createRequiredFormRule('请输入邮箱')
};
const rules = computed(() => {
  const result = { ...constRules };
  if (props.type === 'edit') {
    result.nickname = createRequiredFormRule('请输入昵称');
  }
  return result;
});

const title = computed(() => {
  const titles: Record<ModalType, string> = {
    add: '创建用户',
    edit: '编辑用户'
  };
  return titles[props.type];
});

function createDefaultFormModel(): FormModel {
  return {
    nickname: '',
    email: '',
    password: '',
    status: 1
  };
}

function handleUpdateFormModel(model: Partial<FormModel>) {
  Object.assign(formModel, { ...createDefaultFormModel(), ...model });
}

async function handleSubmit() {
  if (!props.editData) {
    return;
  }
  await formRef.value?.validate();
  let password = formModel.password;
  if (password.length > 0) {
    password = hashPassword(password);
  }
  let response;
  if (props.type === 'add') {
    response = await createMember(props.editData.id, {
      nickname: formModel.nickname,
      email: formModel.email,
      password,
      status: formModel.status
    });
  } else if (props.editData) {
    response = await updateMember(props.editData.id, {
      nickname: formModel.nickname,
      email: formModel.email,
      password,
      status: formModel.status
    });
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
      if (props.type === 'add') {
        handleUpdateFormModel(createDefaultFormModel());
      } else {
        handleUpdateFormModel(props.editData);
      }
    }
  }
);
</script>

<style scoped></style>
