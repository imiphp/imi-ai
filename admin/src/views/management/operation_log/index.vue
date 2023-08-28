<template>
  <div class="overflow-hidden">
    <n-card title="后台操作日志" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="对象">
              <n-select
                v-model:value="listParams.object"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.AdminOperationLogObject ?? [], { text: '全部', value: '' })"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="状态">
              <n-select
                v-model:value="listParams.status"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.AdminOperationLogStatus ?? [])"
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
          scroll-x="1200"
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
import type { DataTableColumns } from 'naive-ui';
import dayjs from 'dayjs';
import { SearchSharp } from '@vicons/ionicons5';
import { fetchAdminOperationLogList } from '@/service';
import { useLoading } from '@/hooks';
import { defaultPaginationProps } from '~/src/utils';
import { useAdminEnums, parseEnumWithAll } from '~/src/store';

const { loading, startLoading, endLoading } = useLoading(false);

const enums = ref<any>({});
const listParams = ref({
  memberId: 0,
  object: '',
  status: 0,
  timeRange: ref<[number, number] | null>(null)
});

const tableData = ref<AdminOperationLog.Log[]>([]);

const pagination = defaultPaginationProps(getTableData);

function setTableData(response: AdminOperationLog.LogListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchAdminOperationLogList(
      listParams.value.memberId,
      listParams.value.object,
      listParams.value.status,
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

const columns: Ref<DataTableColumns<AdminOperationLog.Log>> = ref([
  {
    key: 'id',
    title: 'ID',
    width: 100
  },
  {
    key: 'member',
    title: '用户',
    width: 140,
    render: row => {
      return (
        <>
          <p>ID：{row.memberId}</p>
          <p>昵称：{row.memberInfo.nickname}</p>
        </>
      );
    }
  },
  {
    key: 'objectText',
    title: '对象',
    width: 120
  },
  {
    key: 'statusText',
    title: '状态',
    width: 100
  },
  {
    key: 'message',
    title: '消息',
    width: 300
  },
  {
    key: 'info',
    title: '信息',
    width: 220,
    render: row => {
      return (
        <>
          <p>IP：{row.ip}</p>
          <p>时间：{row.time > 0 ? dayjs(row.time).format('YYYY-MM-DD HH:mm:ss.SSS') : ''}</p>
        </>
      );
    }
  }
]) as Ref<DataTableColumns<AdminOperationLog.Log>>;

async function init() {
  enums.value = await useAdminEnums(['AdminOperationLogObject', 'AdminOperationLogStatus']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
