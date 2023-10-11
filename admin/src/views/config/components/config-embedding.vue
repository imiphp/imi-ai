<template>
  <n-form
    ref="form"
    :rules="rules"
    :label-placement="formLabelPlacement"
    :label-width="formLabelWidth"
    class="max-w-[1280px]"
    :show-feedback="false"
  >
    <n-space vertical>
      <n-card title="训练">
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="压缩文件最大尺寸：">
            <n-input-number v-model:value="formData.maxCompressedFileSize" :min="0">
              <template #suffix>字节</template>
            </n-input-number>
          </n-form-item-grid-item>
          <n-form-item-grid-item label="单个文件最大尺寸：">
            <n-input-number v-model:value="formData.maxSingleFileSize" :min="0">
              <template #suffix>字节</template>
            </n-input-number>
          </n-form-item-grid-item>
          <n-form-item-grid-item label="所有文件最大尺寸：">
            <n-input-number v-model:value="formData.maxTotalFilesSize" :min="0">
              <template #suffix>字节</template>
            </n-input-number>
          </n-form-item-grid-item>
          <n-form-item-grid-item label="段落最大Token数量：">
            <n-input-number v-model:value="formData.maxSectionTokens" :min="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="训练模型配置：" label-placement="top">
            <model-manager
              v-model:modelConfigs="formData.embeddingModelConfigs"
              :models="props.value['config:openai'].config.models"
            />
          </n-form-item-grid-item>
        </n-grid>
      </n-card>
      <n-card title="对话">
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="最多携带段落数量：">
            <n-input-number v-model:value="formData.chatTopSections" :min="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="限流单位：">
            <n-select v-model:value="formData.chatRateLimitUnit" :options="rateLimitUnitOptions" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="限流数量：">
            <n-input-number v-model:value="formData.chatRateLimitAmount" :min="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="匹配相似度：">
            <n-input-number v-model:value="formData.similarity" :min="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="对话提示语：">
            <n-input v-model:value="formData.chatPrompt" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="开启公共项目列表审核：" label-placement="left">
            <n-switch v-model:value="formData.enablePublicListReview" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="聊天模型配置：" label-placement="top">
            <model-manager
              v-model:modelConfigs="formData.chatModelConfigs"
              :models="props.value['config:openai'].config.models"
            />
          </n-form-item-grid-item>
        </n-grid>
      </n-card>
    </n-space>
    <n-space class="w-full pt-16px" :size="24" justify="center">
      <n-button class="w-72px" type="primary" @click="handleSave">保存</n-button>
    </n-space>
  </n-form>
</template>

<script setup lang="tsx">
import { ref } from 'vue';
import type { FormInst, FormRules } from 'naive-ui';
import { defineConfigComponent } from '@/store';
import type { ConfigComponentProps, ConfigComponentEmit } from '@/store';
import { rateLimitUnitOptions } from '~/src/utils';
import ModelManager from './model-manager.vue';

const props = defineProps<ConfigComponentProps>();
const emit = defineEmits<ConfigComponentEmit>();
const rules: FormRules = {};
const formData = ref({
  maxCompressedFileSize: 0,
  maxSingleFileSize: 0,
  maxTotalFilesSize: 0,
  maxSectionTokens: 0,
  chatTopSections: 0,
  embeddingModelConfigs: [] as any[],
  chatModelConfigs: [] as any[],
  chatRateLimitUnit: '',
  chatRateLimitAmount: 0,
  similarity: 0,
  chatPrompt: '',
  enablePublicListReview: false
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:embedding',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
