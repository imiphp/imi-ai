<template>
  <n-form
    ref="form"
    :rules="rules"
    :label-placement="formLabelPlacement"
    :label-width="formLabelWidth"
    class="max-w-full"
    :show-feedback="false"
  >
    <n-space vertical>
      <n-card title="接口配置">
        <api-manager v-model:apis="formData.apis" :models="props.value['config:openai'].config.models" />
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
import ApiManager from './api-manager.vue';

const props = defineProps<ConfigComponentProps>();
const emit = defineEmits<ConfigComponentEmit>();
const rules: FormRules = {};
const formData = ref({
  apis: []
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:openai',
  props,
  emit,
  formData,
  form
);
</script>

<style scoped></style>
