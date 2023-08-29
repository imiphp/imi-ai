<template>
  <div class="overflow-hidden">
    <n-card title="卡包列表" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="过期">
              <n-select
                v-model:value="listParams.expired"
                class="!w-[140px]"
                :options="[
                  {
                    text: '不限',
                    value: undefined
                  },
                  {
                    text: '可用',
                    value: 0
                  },
                  {
                    text: '已过期',
                    value: 1
                  }
                ]"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item>
              <n-button attr-type="submit" type="primary" @click="getTableData">
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
          scroll-x="1024"
          flex-height
          remote
          class="flex-1-hidden"
        />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { ref } from 'vue';
import type { Ref } from 'vue';
import { useRoute } from 'vue-router';
import type { DataTableColumns } from 'naive-ui';
import { SearchSharp } from '@vicons/ionicons5';
import { fetchCardList } from '@/service';
import { useLoading } from '@/hooks';
import { useEnums } from '~/src/store';
import { defaultPaginationProps } from '~/src/utils';

const route = useRoute();
const { loading, startLoading, endLoading } = useLoading(false);

const enums = ref<any>({});
const listParams = ref({
  memberId: parseInt(route.query.memberId?.toString() ?? '0'),
  expired: 0,
  type: 0
});

const pagination = defaultPaginationProps(getTableData);

const tableData = ref<Card.Card[]>([]);
function setTableData(response: Card.CardListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchCardList(
      listParams.value.memberId,
      listParams.value.type,
      listParams.value.expired,
      pagination.page,
      pagination.pageSize
    );
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const columns: Ref<DataTableColumns<Card.Card>> = ref([
  {
    title: '卡号',
    key: 'recordId',
    width: 160,
    render: row => {
      return (
        <>
          <p>ID：{row.id}</p>
          <p>加密ID：{row.recordId}</p>
        </>
      );
    }
  },
  {
    key: 'member',
    title: '用户',
    render: row => {
      return (
        <>
          <p>
            ID：{row.memberId} ({row.memberInfo.recordId})
          </p>
          <p>昵称：{row.memberInfo.nickname}</p>
        </>
      );
    }
  },
  {
    title: '名称',
    key: 'cardType.name'
  },
  {
    title: '余额/面额',
    key: 'amount',
    minWidth: 250,
    render(row) {
      if (row.type === 1) {
        return row.leftAmountText;
      }

      const percent = (row.leftAmount / row.amount) * 100;
      let status = 'default';
      let color;
      if (row.expired) {
        color = 'gray';
      } else if (percent <= 60) status = 'warning';
      else if (percent <= 20) status = 'error';

      return (
        <n-progress
          color={color}
          /* naive-ui bug，下个版本修复 */
          /* indicatorPlacement="inside" */
          percentage={parseFloat(percent.toFixed(2))}
          status={status}
        >
          {{
            default: () => `${row.leftAmountText}/${row.amountText}`
          }}
        </n-progress>
      );
    }
  },
  {
    title: '激活时间',
    key: 'activationTime',
    render(row) {
      return new Date(row.activationTime * 1000).toLocaleString();
    }
  },
  {
    title: '过期时间',
    key: 'expireTime',
    render(row) {
      return row.expireTime > 0
        ? new Date(row.expireTime * 1000).toLocaleString() + (row.expired ? '（已过期）' : '')
        : '永久有效';
    }
  }
]) as Ref<DataTableColumns<Card.Card>>;

async function init() {
  enums.value = await useEnums(['BusinessType', 'OperationType']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
