<template>
  <n-scrollbar x-scrollable>
    <n-table :single-line="false" striped class="w-max min-w-full api-manager-table">
      <thead>
        <tr class="text-center">
          <th width="150">名称</th>
          <th width="300">接口地址</th>
          <th width="200">密钥</th>
          <th width="220">代理</th>
          <th width="100">限流</th>
          <th width="200">模型</th>
          <th width="140">客户端</th>
          <th width="150">操作</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in apis" :key="index">
          <td><n-input v-model:value="item.name" /></td>
          <td><n-dynamic-input v-model:value="item.baseUrls" placeholder="api.openai.com/v1" /></td>
          <td><n-dynamic-input v-model:value="item.apiKeys" /></td>
          <td><n-dynamic-input v-model:value="item.proxys" placeholder="格式：主机名:端口号" /></td>
          <td>
            <n-input-number v-model:value="item.rateLimitAmount" :min="0" />
            <n-select v-model:value="item.rateLimitUnit" :options="rateLimitUnitOptions" />
          </td>
          <td>
            <n-select
              v-model:value="item.models"
              filterable
              tag
              multiple
              :options="modelSelectOptions(models)"
              placeholder="留空不限制"
            ></n-select>
          </td>
          <td><n-select v-model:value="item.client" filterable tag :options="clientListOptions"></n-select></td>
          <td>
            <n-space justify="center">
              <n-switch v-model:value="item.enable">
                <template #checked>启用</template>
                <template #unchecked>禁用</template>
              </n-switch>
              <n-button text type="primary" @click="showCircuitBreakerConfig(index)">熔断设置</n-button>
              <n-popconfirm :on-positive-click="() => handleDelete(index)">
                <template #default>确认删除？</template>
                <template #trigger>
                  <n-button text type="primary">删除</n-button>
                </template>
              </n-popconfirm>
            </n-space>
          </td>
        </tr>
      </tbody>
    </n-table>
    <n-space justify="center" class="mt-2">
      <n-button type="primary" @click="handleAdd">
        <icon-ic-round-plus class="mr-4px text-20px" />
        增加一项
      </n-button>
    </n-space>
  </n-scrollbar>
  <circuit-breaker-modal v-model:visible="circuitBreakerModalVisible" v-model:edit-data="circuitBreakerModalData" />
</template>

<script setup lang="tsx">
import { computed, onMounted, ref } from 'vue';
import { modelSelectOptions } from '@/store';
import { fetchOpenAIClientList } from '~/src/service';
import { rateLimitUnitOptions } from '~/src/utils';
import CircuitBreakerModal from './circuit-breaker-modal.vue';

export interface Props {
  apis: any[];
  models: Config.Model[];
}

export interface Emit {
  (e: 'update:apis', apis: any[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

const circuitBreakerModalVisible = ref(false);
let circuitBreakerModalData: any = null;

const clientList = ref<OpenAI.Client[]>([]);
const clientListOptions = computed(() => {
  return clientList.value.map(item => {
    return {
      label: item.title,
      value: item.class
    };
  });
});

const apis = computed({
  get() {
    return props.apis;
  },
  set(value) {
    emit('update:apis', value);
  }
});

function handleDelete(index: number) {
  apis.value.splice(index, 1);
}

function handleAdd() {
  apis.value.push({
    name: '',
    baseUrls: [],
    apiKeys: [],
    proxys: [],
    rateLimitUnit: 'second',
    rateLimitAmount: 0,
    models: [],
    client: '',
    enable: true
  });
}

function showCircuitBreakerConfig(index: number) {
  circuitBreakerModalData = computed({
    get() {
      return apis.value[index].circuitBreaker;
    },
    set(newValue) {
      apis.value[index].circuitBreaker = newValue;
    }
  });
  circuitBreakerModalVisible.value = true;
}

onMounted(async () => {
  const { data } = await fetchOpenAIClientList();
  clientList.value = (data as any).list;
});
</script>

<style lang="less">
.api-manager-table {
  .n-dynamic-input-item__action {
    margin-left: 4px !important;
  }
  td {
    padding: 6px !important;
  }
}
</style>
