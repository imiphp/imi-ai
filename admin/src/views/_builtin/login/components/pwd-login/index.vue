<template>
  <n-form ref="formRef" :model="model" :rules="rules" size="large" :show-label="false">
    <n-form-item path="account">
      <n-input v-model:value="model.account" :placeholder="$t('page.login.common.userNamePlaceholder')" />
    </n-form-item>
    <n-form-item path="password">
      <n-input
        v-model:value="model.password"
        type="password"
        show-password-on="click"
        :placeholder="$t('page.login.common.passwordPlaceholder')"
      />
    </n-form-item>
    <n-form-item path="vcode" label="图形验证码">
      <n-input ref="inputVcode" v-model:value="model.vcode" />
      <Vcode ref="vcode" v-model:token="model.vcodeToken" />
    </n-form-item>
    <n-space :vertical="true" :size="24">
      <n-button
        attr-type="submit"
        type="primary"
        size="large"
        :block="true"
        :round="true"
        :loading="auth.loginLoading"
        @click="handleSubmit"
      >
        {{ $t('page.login.common.confirm') }}
      </n-button>
    </n-space>
  </n-form>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import type { FormInst, FormRules } from 'naive-ui';
import { useAuthStore } from '@/store';
import { formRules } from '@/utils';

const auth = useAuthStore();
const { login } = useAuthStore();

const formRef = ref<HTMLElement & FormInst>();

const model = reactive({
  account: '',
  password: '',
  vcode: '',
  vcodeToken: ''
});

const rules: FormRules = {
  password: formRules.pwd
};

async function handleSubmit() {
  await formRef.value?.validate();

  const { account, password, vcode, vcodeToken } = model;

  login(account, password, vcode, vcodeToken);
}
</script>

<style scoped></style>
