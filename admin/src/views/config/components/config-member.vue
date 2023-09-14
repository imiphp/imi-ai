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
          <n-form-item-grid-item label="登录Token有效时长：">
            <n-input-number v-model:value="formData.tokenExpires" :min="0">
              <template #suffix>秒</template>
            </n-input-number>
            <span class="ml-2">{{ timespanHuman(formData.tokenExpires) }}</span>
          </n-form-item-grid-item>
          <n-form-item-grid-item label="启用邮箱黑名单：" label-placement="left">
            <n-switch v-model:value="formData.enableEmailBlackList" />
          </n-form-item-grid-item>
        </n-grid>
      </n-card>
      <n-card title="邮箱注册">
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="启用邮箱注册：" label-placement="left">
            <n-switch v-model:value="formData.emailRegister" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="注册验证码有效时长：">
            <n-input-number v-model:value="formData.registerCodeTTL" :min="0">
              <template #suffix>秒</template>
            </n-input-number>
            <span class="ml-2">{{ timespanHuman(formData.registerCodeTTL) }}</span>
          </n-form-item-grid-item>
          <n-form-item-grid-item label="注册邮件标题：">
            <n-input v-model:value="formData.registerEmailTitle" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="注册邮件内容：">
            <n-input v-model:value="formData.registerEmailContent" type="textarea" :rows="5" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="注册邮件是否html：" label-placement="left">
            <n-switch v-model:value="formData.registerEmailIsHtml" />
          </n-form-item-grid-item>
        </n-grid>
      </n-card>
      <n-card title="邀请">
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="启用邀请机制：" label-placement="left">
            <n-switch v-model:value="formData.enableInvitation" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="邀请人获得奖励金额：">
            <n-input-number v-model:value="formData.inviterGiftAmount" :min="0" />
          </n-form-item-grid-item>
          <n-form-item-grid-item label="被邀请人获得奖励金额：">
            <n-input-number v-model:value="formData.inviteeGiftAmount" :min="0" />
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
  emailRegister: false,
  registerCodeTTL: 0,
  registerEmailTitle: '',
  registerEmailContent: '',
  registerEmailIsHtml: false,
  tokenExpires: 0,
  enableInvitation: false,
  enableInputInvitation: false,
  inviterGiftAmount: 0,
  inviteeGiftAmount: 0,
  enableEmailBlackList: false
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:member',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
