<template>
  <div class="overflow-hidden">
    <n-card title="支付订单列表" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item>
              <n-input v-model:value="listParams.search" clearable placeholder="交易单号搜索" />
            </n-form-item>
            <n-form-item label="交易类型">
              <n-select
                v-model:value="listParams.type"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.PaymentOrderType ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="业务类型">
              <n-select
                v-model:value="listParams.businessType"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.PaymentBusinessType ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="支付渠道">
              <n-select
                v-model:value="listParams.channel"
                class="!w-[140px]"
                :options="channelList"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="二级支付渠道">
              <n-select
                v-model:value="listParams.secondaryChannelId"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.PaymentSecondaryPaymentChannel ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="三级支付渠道">
              <n-select
                v-model:value="listParams.tertiaryChannelId"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.PaymentTertiaryPaymentChannel ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="开始时间">
              <n-date-picker v-model:value="listParams.beginTime" type="datetime" clearable />
            </n-form-item>
            <n-form-item label="结束时间">
              <n-date-picker v-model:value="listParams.endTime" type="datetime" clearable />
            </n-form-item>
            <n-form-item>
              <n-button attr-type="submit" type="primary" @click="getTableData(1)">
                <n-icon :component="SearchSharp" size="20" />
                查询
              </n-button>
            </n-form-item>
          </n-space>
        </n-form>
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="row => row.id"
          scroll-x="1360"
          flex-height
          remote
          class="flex-1-hidden"
        />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { computed, ref } from 'vue';
import type { Ref } from 'vue';
import type { DataTableColumns } from 'naive-ui';
import dayjs from 'dayjs';
import { SearchSharp } from '@vicons/ionicons5';
import { fetchPaymentOrderList, getConfig } from '@/service';
import { useLoading } from '@/hooks';
import { defaultPaginationProps } from '~/src/utils';
import { useAdminEnums, parseEnumWithAll } from '~/src/store';

const { loading, startLoading, endLoading } = useLoading(false);

const config = ref<any>({});
const enums = ref<any>({});
const listParams = ref({
  search: '',
  businessType: 0,
  memberId: 0,
  type: 0,
  channel: 0,
  secondaryChannelId: 0,
  tertiaryChannelId: 0,
  beginTime: undefined as number | undefined,
  endTime: undefined as number | undefined,
  page: 1,
  limit: 15
});
const channelList = computed(() => {
  return parseEnumWithAll(
    (config?.value['config:payment']?.config?.channels ?? []).map((item: any) => {
      return {
        text: item.title,
        value: item.name
      };
    })
  );
});

const tableData = ref<Payment.Order[]>([]);

const pagination = defaultPaginationProps(getTableData);

function setTableData(response: Payment.OrderResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData(page: number | null = null) {
  startLoading();
  if (page !== null) {
    pagination.page = page;
  }
  try {
    const params = { ...listParams.value };
    if (params.beginTime) params.beginTime /= 1000;
    if (params.endTime) params.endTime /= 1000;
    const { data } = await fetchPaymentOrderList(params);
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const columns: Ref<DataTableColumns<Payment.Order>> = ref([
  {
    key: 'id',
    title: 'ID',
    width: 200,
    render: row => {
      return (
        <>
          <p>id：{row.id}</p>
          <p>订单号：{row.tradeNo}</p>
          <p>支付渠道单号：{row.channelTradeNo}</p>
          <p>二级支付渠道单号：{row.secondaryTradeNo}</p>
          <p>三级支付渠道单号：{row.tertiaryTradeNo}</p>
        </>
      );
    }
  },
  {
    key: 'type',
    title: '支付渠道',
    width: 150,
    render: row => {
      return (
        <>
          <p>支付渠道：{row.channelTitle}</p>
          <p>二级支付渠道：{row.secondaryChannelTitle}</p>
          <p>三级支付渠道：{row.tertiaryChannelTitle}</p>
        </>
      );
    }
  },
  {
    key: 'type',
    title: '类型',
    width: 150,
    render: row => {
      return (
        <>
          <p>交易类型：{row.typeTitle}</p>
          <p>业务类型：{row.businessTypeTitle}</p>
          <p>业务ID：{row.businessId}</p>
        </>
      );
    }
  },
  {
    key: 'member',
    title: '用户',
    width: 140,
    render: row => {
      if (row.memberInfo)
        return (
          <>
            <p>ID：{row.memberId}</p>
            <p>昵称：{row.memberInfo.nickname}</p>
          </>
        );
      return '';
    }
  },
  {
    key: 'amount',
    title: '金额',
    width: 120,
    render: row => {
      return (
        <>
          <p>金额：{(row.amount / 100).toFixed(2)}元</p>
          <p>剩余：{(row.leftAmount / 100).toFixed(2)}元</p>
        </>
      );
    }
  },
  {
    key: 'remark',
    title: '备注',
    width: 150,
    render: row => {
      return (
        <n-ellipsis expand-trigger="click" line-clamp="5" tooltip={false}>
          <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.remark}</pre>
        </n-ellipsis>
      );
    }
  },
  {
    key: 'time',
    title: '时间',
    width: 220,
    render: row => {
      return (
        <>
          <p>创建时间：{row.createTime > 0 ? dayjs(row.createTime * 1000).format('YYYY-MM-DD HH:mm:ss') : '-'}</p>
          <p>支付时间：{row.payTime > 0 ? dayjs(row.payTime * 1000).format('YYYY-MM-DD HH:mm:ss') : '-'}</p>
          <p>通知时间：{row.notifyTime > 0 ? dayjs(row.notifyTime * 1000).format('YYYY-MM-DD HH:mm:ss') : '-'}</p>
        </>
      );
    }
  }
]) as Ref<DataTableColumns<Payment.Order>>;

async function init() {
  await Promise.all([
    (async () => {
      enums.value = await useAdminEnums([
        'PaymentOrderType',
        'PaymentBusinessType',
        'PaymentSecondaryPaymentChannel',
        'PaymentTertiaryPaymentChannel'
      ]);
    })(),
    (async () => {
      const { data } = await getConfig();
      config.value = (data as any).data;
    })()
  ]);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
