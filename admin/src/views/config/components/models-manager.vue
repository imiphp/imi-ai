<template>
  <n-scrollbar x-scrollable>
    <n-table :single-line="false" striped class="w-max min-w-full">
      <thead>
        <tr class="text-center">
          <th width="120">标题</th>
          <th width="240">模型名称</th>
          <th width="130">Token 输入倍率</th>
          <th width="130">Token 输出倍率</th>
          <th width="130">最大 Token</th>
          <th width="130">额外 Token</th>
          <th width="70">付费标志</th>
          <th>选中提示</th>
          <th width="70">启用</th>
          <th width="70">删除</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in modelConfigsData" :key="index">
          <td>
            <n-input v-model:value="item.title" />
          </td>
          <td>
            <n-input v-model:value="item.model" />
          </td>
          <td>
            <n-input-number v-model:value="item.inputTokenMultiple" :min="0" />
          </td>
          <td>
            <n-input-number v-model:value="item.outputTokenMultiple" :min="0" />
          </td>
          <td><n-input-number v-model:value="item.maxTokens" :min="0" /></td>
          <td>
            <p>
              每条消息额外：
              <n-input-number v-model:value="item.additionalTokensPerMessage" :min="0" />
            </p>
            <p>
              每次提问额外：
              <n-input-number v-model:value="item.additionalTokensAfterMessages" :min="0" />
            </p>
          </td>
          <td class="text-center"><n-switch v-model:value="item.paying" /></td>
          <td><n-input v-model:value="item.tips" type="textarea" :rows="2" /></td>
          <td class="text-center"><n-switch v-model:value="item.enable" /></td>
          <td>
            <n-popconfirm :on-positive-click="() => handleDeleteModel(index)">
              <template #default>确认删除？</template>
              <template #trigger>
                <n-button text block type="primary">删除</n-button>
              </template>
            </n-popconfirm>
          </td>
        </tr>
      </tbody>
    </n-table>
    <n-space justify="center" class="mt-2">
      <n-button type="primary" @click="handleAddModelConfig">
        <icon-ic-round-plus class="mr-4px text-20px" />
        增加一项
      </n-button>
    </n-space>
  </n-scrollbar>
</template>

<script setup lang="tsx">
import { watch, reactive } from 'vue';

export interface Props {
  modelConfigs: any[];
}

export interface Emit {
  (e: 'update:modelConfigs', modelConfigs: any[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

const modelConfigsData = reactive(
  (() => {
    return props.modelConfigs.map(item => {
      return {
        ...item,
        enable: item.enable === undefined ? true : item.enable,
        inputTokenMultiple: parseFloat(item.inputTokenMultiple),
        outputTokenMultiple: parseFloat(item.outputTokenMultiple)
      };
    });
  })()
);
watch(modelConfigsData, value => {
  emit('update:modelConfigs', value);
});

function handleDeleteModel(index: number) {
  modelConfigsData.splice(index, 1);
}

function handleAddModelConfig() {
  modelConfigsData.push({
    title: '',
    model: '',
    inputTokenMultiple: 1,
    outputTokenMultiple: 1,
    maxTokens: 4096,
    tips: '',
    enable: true,
    additionalTokensPerMessage: 0,
    additionalTokensAfterMessages: 0
  });
}
</script>

<style scoped></style>
