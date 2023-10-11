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
      <n-card title="限流">
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="限流单位：">
            <n-select v-model:value="formData.rateLimitUnit" :options="rateLimitUnitOptions" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="限流数量：">
            <n-input-number v-model:value="formData.rateLimitAmount" :min="0" />
          </n-form-item-grid-item>
        </n-grid>
      </n-card>
      <n-card title="模型配置">
        <model-manager
          v-model:modelConfigs="formData.modelConfigs"
          :models="props.value['config:openai'].config.models"
        />
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
  modelConfigs: [] as any[],
  rateLimitUnit: '',
  rateLimitAmount: 0
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:chat',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
