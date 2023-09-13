<template>
  <n-modal
    v-model:show="modalVisible"
    preset="card"
    :title="title"
    class="w-1280px max-w-full h-[600px] max-h-full edit-prompt-modal"
  >
    <div class="flex-col h-full">
      <n-form
        ref="formRef"
        label-placement="left"
        :label-width="150"
        :model="formModel"
        :rules="rules"
        class="flex-1 overflow-auto h-0"
      >
        <n-tabs
          display-directive="show"
          class="h-full"
          pane-wrapper-class="max-h-full"
          pane-class="max-h-full overflow-y-auto"
        >
          <n-tab-pane name="common" tab="通用">
            <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
              <n-form-item-grid-item label="类型" path="type">
                <n-select
                  v-model:value="formModel.type"
                  class="!w-[140px]"
                  :options="typeSelectOptions"
                  label-field="text"
                  value-field="value"
                />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="分类" path="type">
                <n-select
                  v-model:value="formModel.categoryIds"
                  class="!w-[360px]"
                  filterable
                  tag
                  multiple
                  :options="categorySelectOptions"
                  placeholder="留空不限制"
                ></n-select>
              </n-form-item-grid-item>
              <n-form-item-grid-item label="标题" path="title">
                <n-input v-model:value="formModel.title" />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="简介">
                <n-input v-model:value="formModel.description" type="textarea" rows="3" />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="提示语">
                <div class="flex-col w-full">
                  <n-input ref="inputPrompt" v-model:value="formModel.prompt" type="textarea" rows="5" />
                  <n-space class="mt-2">
                    <n-button
                      v-for="(item, index) in formModel.formConfig"
                      :key="index"
                      type="success"
                      size="tiny"
                      @click="insertPromptParams(item.id)"
                    >
                      {{ item.id }}
                    </n-button>
                  </n-space>
                </div>
              </n-form-item-grid-item>
              <n-form-item-grid-item label="首条消息内容">
                <div class="flex-col w-full">
                  <n-input
                    ref="inputFirstMessage"
                    v-model:value="formModel.firstMessageContent"
                    type="textarea"
                    rows="5"
                  />
                  <n-space class="mt-2">
                    <n-button
                      v-for="(item, index) in formModel.formConfig"
                      :key="index"
                      type="success"
                      size="tiny"
                      @click="insertFirstMessageParams(item.id)"
                    >
                      {{ item.id }}
                    </n-button>
                  </n-space>
                </div>
              </n-form-item-grid-item>
              <n-form-item-grid-item label="排序">
                <n-input-number v-model:value="formModel.index" :min="0" :max="255" />
              </n-form-item-grid-item>
            </n-grid>
          </n-tab-pane>
          <n-tab-pane name="model" tab="模型参数">
            <n-grid cols="1 s:2" :x-gap="18" item-responsive responsive="screen">
              <n-form-item-grid-item label="模型" path="config.model">
                <n-select
                  v-model:value="formModel.config.model"
                  class="!w-[200px]"
                  :options="modelSelectOptions(config['config:openai'].config.models)"
                />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="Temperature" path="config.temperature">
                <n-slider v-model:value="formModel.config.temperature" :max="2" :min="0" :step="0.1" show-tooltip />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="top_p" path="config.top_p">
                <n-slider v-model:value="formModel.config.top_p" :max="1" :min="0" :step="0.1" show-tooltip />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="presence_penalty" path="config.presence_penalty">
                <n-slider
                  v-model:value="formModel.config.presence_penalty"
                  :max="2"
                  :min="-2"
                  :step="0.1"
                  show-tooltip
                />
              </n-form-item-grid-item>
              <n-form-item-grid-item label="frequency_penalty" path="config.frequency_penalty">
                <n-slider
                  v-model:value="formModel.config.frequency_penalty"
                  :max="2"
                  :min="-2"
                  :step="0.1"
                  show-tooltip
                />
              </n-form-item-grid-item>
            </n-grid>
          </n-tab-pane>
          <n-tab-pane v-if="2 === formModel.type" name="form" tab="表单配置">
            <form-config v-model:value="formModel.formConfig" />
          </n-tab-pane>
        </n-tabs>
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
import type { FormInst, FormItemRule, InputInst } from 'naive-ui';
import { modelSelectOptions } from '@/store';
import { createRequiredFormRule } from '@/utils';
import { createPrompt, updatePrompt } from '~/src/service';
import FormConfig from './form-config.vue';

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
  typeSelectOptions: any[];
  categorySelectOptions: any[];
  config: any;
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
    add: '新建提示语',
    edit: '编辑提示语'
  };
  return titles[props.type];
});

const inputPrompt = ref<InputInst>();
const inputFirstMessage = ref<InputInst>();

const formRef = ref<HTMLElement & FormInst>();

type FormModel = {
  id: number;
  type: number;
  categoryIds: number[];
  title: string;
  description: string;
  prompt: string;
  firstMessageContent: string;
  index: number;
  config: any;
  formConfig: any[];
};

const formModel = reactive<FormModel>(createDefaultFormModel());

const rules: Record<string, FormItemRule | FormItemRule[]> = {
  type: createRequiredFormRule('请选择类型'),
  title: createRequiredFormRule('请输入标题')
};

function createDefaultFormModel(): FormModel {
  return {
    id: 0,
    type: 1,
    categoryIds: [],
    title: '',
    description: '',
    prompt: '',
    firstMessageContent: '',
    index: 128,
    config: {},
    formConfig: []
  };
}

function insertPromptParams(paramId: string) {
  const inputElement = inputPrompt.value?.textareaElRef;
  if (!inputElement) return;
  // 插入光标位置
  const cursorPosition = inputElement.selectionStart;
  const inputValue = formModel.prompt;
  const newValue = `${inputValue.slice(0, cursorPosition)}{${paramId}}${inputValue.slice(cursorPosition)}`;
  formModel.prompt = newValue;
  inputElement.focus();
  // 设置光标位置
  setTimeout(() => {
    inputElement.setSelectionRange(cursorPosition + paramId.length + 2, cursorPosition + paramId.length + 2);
  }, 0);
}

function insertFirstMessageParams(paramId: string) {
  const inputElement = inputFirstMessage.value?.textareaElRef;
  if (!inputElement) return;
  // 插入光标位置
  const cursorPosition = inputElement.selectionStart;
  const inputValue = formModel.firstMessageContent;
  const newValue = `${inputValue.slice(0, cursorPosition)}{${paramId}}${inputValue.slice(cursorPosition)}`;
  formModel.firstMessageContent = newValue;
  inputElement.focus();
  // 设置光标位置
  setTimeout(() => {
    inputElement.setSelectionRange(cursorPosition + paramId.length + 2, cursorPosition + paramId.length + 2);
  }, 0);
}

function handleUpdateFormModel(model: Partial<FormModel>) {
  Object.assign(formModel, { ...createDefaultFormModel(), ...model });
  if (!formModel.config || Object.keys(formModel.config).length === 0 || Array.isArray(formModel.config)) {
    formModel.config = {
      model: 'gpt-3.5-turbo',
      temperature: 1,
      top_p: 1,
      presence_penalty: 0,
      frequency_penalty: 0
    };
  }
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
    response = await createPrompt(
      formModel.type,
      formModel.categoryIds,
      formModel.title,
      formModel.description,
      formModel.prompt,
      formModel.firstMessageContent,
      formModel.index,
      formModel.config,
      formModel.formConfig
    );
  } else if (props.editData) {
    response = await updatePrompt(props.editData.id, { ...(formModel as any) });
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
