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
          <n-form-item-grid-item label="发信邮箱：">
            <n-input v-model:value="formData.fromAddress" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="发信人：">
            <n-input v-model:value="formData.fromName" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="SMTP服务器地址：">
            <n-input v-model:value="formData.host" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="SMTP服务器端口：">
            <n-input-number v-model:value="formData.port" :min="0" :max="65535" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="SMTP安全协议：">
            <n-select
              v-model:value="formData.secure"
              :options="[
                { label: '无', value: '' },
                { label: 'SSL', value: 'ssl' },
                { label: 'TLS', value: 'tls' }
              ]"
              filterable
              tag
            />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="启用验证：">
            <n-switch v-model:value="formData.auth" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="SMTP用户名：">
            <n-input v-model:value="formData.username" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="SMTP密码：">
            <n-input v-model:value="formData.password" type="password" show-password-on="click" />
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
  fromAddress: '',
  fromName: '',
  host: '',
  port: 0,
  secure: '',
  auth: false,
  username: '',
  password: ''
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:email',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
