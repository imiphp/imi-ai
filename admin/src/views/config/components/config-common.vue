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
          <n-form-item-grid-item label="接口地址：">
            <n-input v-model:value="formData.apiUrl" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="前端地址：">
            <n-input v-model:value="formData.webUrl" />
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

const props = defineProps<ConfigComponentProps>();
const emit = defineEmits<ConfigComponentEmit>();
const rules: FormRules = {};
const formData = ref({
  apiUrl: '',
  webUrl: ''
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:common',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
