<template>
  <div class="overflow-hidden">
    <n-card title="用户交易明细" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="业务类型">
              <n-select
                v-model:value="listParams.businessType"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.BusinessType ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="操作类型">
              <n-select
                v-model:value="listParams.operationType"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.OperationType ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="时间">
              <NDatePicker v-model:value="listParams.timeRange" class="pr-2 flex-1" type="daterange" clearable />
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
import { fetchMemberCardDetails } from '@/service';
import { useLoading } from '@/hooks';
import { useAdminEnums, parseEnumWithAll } from '~/src/store';
import { defaultPaginationProps } from '~/src/utils';

const route = useRoute();
const memberId = parseInt(route.query.memberId?.toString() ?? '0');
const { loading, startLoading, endLoading } = useLoading(false);

const enums = ref<any>({});
const listParams = ref({
  operationType: 0,
  businessType: 0,
  timeRange: ref<[number, number] | null>(null)
});

const pagination = defaultPaginationProps(getTableData);

const tableData = ref<Card.MemberCardOrder[]>([]);
function setTableData(response: Card.MemberCardOrderListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchMemberCardDetails(
      memberId,
      listParams.value.operationType,
      listParams.value.businessType,
      listParams.value.timeRange ? parseInt((listParams.value.timeRange[0] / 1000).toString()) : 0,
      listParams.value.timeRange ? parseInt((listParams.value.timeRange[1] / 1000 + 86399).toString()) : 0,
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

const columns: Ref<DataTableColumns<Card.MemberCardOrder>> = ref([
  {
    title: 'ID',
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
    title: '业务类型',
    key: 'businessTypeText'
  },
  {
    title: '操作类型',
    key: 'operationTypeText'
  },
  {
    title: '变动金额',
    key: 'changeAmount',
    render(row) {
      return row.isDeduct ? (
        <span class="text-[#18a058]">-{row.changeAmount}</span>
      ) : (
        <span class="text-[#d03050]">+{row.changeAmount}</span>
      );
    }
  },
  {
    title: '时间',
    key: 'time',
    render(row) {
      return new Date(row.time * 1000).toLocaleString();
    }
  }
]) as Ref<DataTableColumns<Card.MemberCardOrder>>;

async function init() {
  enums.value = await useAdminEnums(['BusinessType', 'OperationType']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
