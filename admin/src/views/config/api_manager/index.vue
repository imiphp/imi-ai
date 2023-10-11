<template>
  <div class="overflow-hidden">
    <n-card title="接口管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-table :single-line="false" striped class="w-max min-w-full api-manager-table">
          <thead>
            <tr class="text-center">
              <th width="120">名称</th>
              <th width="200">接口地址</th>
              <th width="200">密钥</th>
              <th width="200">代理</th>
              <th width="250">信息</th>
              <th width="150">熔断</th>
              <th width="150">操作</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in config['config:openai']?.config.apis" :key="index">
              <td>{{ item.name }}</td>
              <td>
                <p v-for="(value, i) in item.baseUrls" :key="i">{{ value }}</p>
              </td>
              <td>
                <p v-for="(value, i) in item.apiKeys" :key="i">{{ value }}</p>
              </td>
              <td>
                <p v-for="(value, i) in item.proxys" :key="i">{{ value }}</p>
              </td>
              <td>
                <p>
                  <b>限流条件：</b>
                  <template v-if="item.rateLimitAmount > 0">
                    {{ item.rateLimitAmount }}次/{{ getRateLimitLabel(item.rateLimitUnit) }}
                  </template>
                  <template v-else>未启用</template>
                </p>
                <p v-if="item.leftRateLimitAmount > 0">
                  <b>限流剩余次数：</b>
                  {{ item.leftRateLimitAmount }}次
                </p>
                <p>
                  <b>模型：</b>
                  <template v-if="item.models.length === 0">所有</template>
                </p>
                <template v-if="item.models.length > 0">
                  <p v-for="(value, i) in item.models" :key="i">{{ value }}</p>
                </template>
                <p>
                  <b>客户端：</b>
                  {{ item.client }}
                </p>
              </td>
              <td>
                <p>
                  <b>熔断条件：</b>
                  <template v-if="item.circuitBreaker.limitAmount > 0">
                    {{ item.circuitBreaker.limitAmount }}次/{{ getRateLimitLabel(item.circuitBreaker.limitUnit) }}
                  </template>
                  <template v-else>未启用</template>
                </p>
                <p v-if="item.circuitBreaker.limitAmount > 0">
                  <b>剩余次数：</b>
                  {{ item.circuitBreaker.leftLimitAmount }}次
                </p>
                <p>
                  <b>熔断时长：</b>
                  {{ timespanHuman(item.circuitBreaker.breakDuration) }}
                </p>
                <p v-if="new Date().getTime() / 1000 < item.circuitBreaker.availableBeginTime">
                  <b>熔断解除时间：</b>
                  {{ new Date(item.circuitBreaker.availableBeginTime * 1000).toLocaleString() }}
                </p>
              </td>
              <td>
                <n-space justify="center">
                  <n-switch v-model:value="item.enable">
                    <template #checked>启用</template>
                    <template #unchecked>禁用</template>
                  </n-switch>
                </n-space>
              </td>
            </tr>
          </tbody>
        </n-table>
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { onMounted, ref } from 'vue';
import { getConfig } from '~/src/service';
import { getRateLimitLabel, timespanHuman } from '~/src/utils';

const config = ref<any>({});

onMounted(async () => {
  const { data } = await getConfig();
  config.value = (data as any).data;
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
