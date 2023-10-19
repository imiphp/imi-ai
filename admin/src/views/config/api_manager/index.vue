<template>
  <div class="overflow-hidden">
    <n-card title="接口管理" :bordered="false" class="h-full rounded-8px shadow-sm overflow-auto">
      <n-data-table :columns="columns" :data="list" scroll-x="1280" remote class="flex-1-hidden" />
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import type { Ref } from 'vue';
import { computed, onMounted, ref } from 'vue';
import type { DataTableColumns } from 'naive-ui';
import { useDialog } from 'naive-ui';
import { getConfig, saveConfig } from '~/src/service';
import { getRateLimitLabel, timespanHuman } from '~/src/utils';

const dialog = useDialog();

const config = ref<any>({});

const list = computed(() => {
  return config.value['config:openai']?.config.apis;
});

const columns: Ref<DataTableColumns<any>> = ref([
  {
    title: '名称',
    key: 'name',
    width: 120
  },
  {
    title: '接口地址',
    key: 'baseUrls',
    width: 200,
    render: row => {
      const result = [];
      for (const value of row.baseUrls) {
        result.push(<p>{value}</p>);
      }
      return result;
    }
  },
  {
    title: '密钥',
    key: 'apiKeys',
    width: 200,
    render: row => {
      const result = [];
      for (const value of row.apiKeys) {
        result.push(<p>{value}</p>);
      }
      return result;
    }
  },
  {
    title: '信息',
    key: 'info',
    width: 250,
    render: row => {
      let leftRateLimitAmount;
      if (row.leftRateLimitAmount > 0) {
        leftRateLimitAmount = (
          <p>
            <b>限流剩余次数：</b>
            {row.leftRateLimitAmount}次
          </p>
        );
      }
      let models;
      if (row.models.length === 0) {
        models = '所有';
      } else {
        models = [];
        for (const value of row.models) {
          models.push(<p>{value}</p>);
        }
      }
      return (
        <>
          <p>
            <b>限流条件：</b>
            {row.rateLimitAmount > 0 ? `${row.rateLimitAmount}次/${getRateLimitLabel(row.rateLimitUnit)}` : '未启用'}
          </p>
          {leftRateLimitAmount}
          <p>
            <b>模型：</b>
            {models}
          </p>
          <p>
            <b>客户端：</b>
            {row.client}
          </p>
        </>
      );
    }
  },
  {
    title: '熔断',
    key: 'circuitBreaker',
    width: 150,
    render: row => {
      let leftLimitAmount;
      if (row.circuitBreaker.limitAmount > 0) {
        leftLimitAmount = (
          <p>
            <b>剩余次数：</b>
            {row.circuitBreaker.leftLimitAmount}次
          </p>
        );
      }
      let availableBeginTime;
      if (new Date().getTime() / 1000 < row.circuitBreaker.availableBeginTime) {
        availableBeginTime = (
          <p>
            <b>熔断解除时间：</b>
            {new Date(row.circuitBreaker.availableBeginTime * 1000).toLocaleString()}
          </p>
        );
      }
      return (
        <>
          <p>
            <b>熔断条件：</b>
            {row.circuitBreaker.limitAmount > 0
              ? `${row.circuitBreaker.limitAmount}次/${getRateLimitLabel(row.circuitBreaker.limitUnit)}`
              : '未启用'}
          </p>
          {leftLimitAmount}
          <p>
            <b>熔断时长：</b>
            {timespanHuman(row.circuitBreaker.breakDuration)}
          </p>
          {availableBeginTime}
        </>
      );
    }
  },
  {
    title: '操作',
    key: 'action',
    width: 150,
    render: (row, rowIndex) => (
      <n-space justify="center">
        <n-switch
          v-model:value={row.enable}
          on-update:value={async (value: boolean) => {
            dialog.warning({
              title: '询问',
              content: `是否${value ? '启用' : '禁用'}？`,
              positiveText: '确定',
              negativeText: '取消',
              onPositiveClick: async () => {
                config.value['config:openai'].config.apis[rowIndex].enable = value;
                const configSaveData = {
                  'config:openai': config.value['config:openai'].config
                };
                const { data } = await saveConfig(configSaveData);
                if (data?.code === 0) {
                  row.enable = value;
                  window.$message?.info('保存成功');
                }
              }
            });
          }}
          v-slots={{
            checked: () => '启用',
            unchecked: () => '禁用'
          }}
        ></n-switch>
      </n-space>
    )
  }
]);

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
