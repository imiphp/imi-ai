<template>
  <div class="overflow-hidden">
    <n-spin :show="loading" class="spin h-full rounded-8px shadow-sm">
      <n-card title="系统设置" :bordered="false" class="h-full" content-style="height: 100%; overflow-y: auto;">
        <div class="flex-col h-full">
          <n-tabs default-value="common" pane-wrapper-style="overflow-y: auto">
            <n-tab-pane name="common" tab="通用设置">
              <config-common v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="card" tab="卡设置">
              <config-card v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="member" tab="账户设置">
              <config-member v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="email" tab="邮箱设置">
              <config-email v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="chat" tab="AI聊天设置">
              <config-chat v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="embedding" tab="模型训练设置">
              <config-embedding v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="openai" tab="OpenAI设置">
              <config-openai v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="prompt" tab="提示语设置">
              <config-prompt v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="vcode" tab="验证码设置">
              <config-vcode v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
            <n-tab-pane name="admin" tab="后台设置">
              <config-admin v-model:value="config" v-model:loading="loading" />
            </n-tab-pane>
          </n-tabs>
        </div>
      </n-card>
    </n-spin>
  </div>
</template>

<script setup lang="tsx">
import { onMounted, ref } from 'vue';
import { getConfig } from '~/src/service/api';
import ConfigCommon from './components/config-common.vue';
import ConfigCard from './components/config-card.vue';
import ConfigMember from './components/config-member.vue';
import ConfigEmail from './components/config-email.vue';
import ConfigChat from './components/config-chat.vue';
import ConfigEmbedding from './components/config-embedding.vue';
import ConfigVcode from './components/config-vcode.vue';
import ConfigAdmin from './components/config-admin.vue';
import ConfigPrompt from './components/config-prompt.vue';
import ConfigOpenai from './components/config-openai.vue';

const config = ref<any>({});
const loading = ref(false);

onMounted(async () => {
  const { data } = await getConfig();
  config.value = (data as any).data;
});
</script>

<style scoped></style>
