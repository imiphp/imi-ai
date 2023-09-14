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
      <n-card>
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="注册赠送余额：">
            <n-input-number v-model:value="formData.registerGiftTokens" :min="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="最多激活失败次数：">
            <n-input-number v-model:value="formData.activationFailedMaxCount" :min="5" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="激活失败超限等待时间：">
            <n-input-number v-model:value="formData.activationFailedWaitTime" :min="0">
              <template #suffix>秒</template>
            </n-input-number>
            <span class="ml-2">{{ timespanHuman(formData.activationFailedWaitTime) }}</span>
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
import { timespanHuman } from '@/utils';

const props = defineProps<ConfigComponentProps>();
const emit = defineEmits<ConfigComponentEmit>();
const rules: FormRules = {};
const formData = ref({
  registerGiftTokens: 0,
  activationFailedMaxCount: 0,
  activationFailedWaitTime: 0
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:card',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
