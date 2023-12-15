<template>
  <div class="overflow-hidden">
    <n-card title="聊天记录" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="row => row.id"
          scroll-x="1000"
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
import { fetchChatMessageList } from '@/service';
import { useLoading } from '@/hooks';
import { defaultPaginationProps } from '~/src/utils';

const route = useRoute();
const sessionId = parseInt(route.query.sessionId?.toString() ?? '0');
const { loading, startLoading, endLoading } = useLoading(false);

const tableData = ref<Chat.Message[]>([]);

const pagination = defaultPaginationProps(getTableData);

function setTableData(response: Chat.MessageListResponse) {
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
    const { data } = await fetchChatMessageList(sessionId, pagination.page, pagination.pageSize);
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const columns: Ref<DataTableColumns<Chat.Message>> = ref([
  {
    key: 'id',
    title: 'ID',
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
    key: 'role',
    title: '角色',
    width: 100
  },
  {
    key: 'tokens',
    title: 'Tokens',
    width: 100
  },
  {
    key: 'message',
    title: '消息',
    width: 520,
    render: row => {
      return (
        <n-ellipsis expand-trigger="click" line-clamp="5" tooltip={false}>
          <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.message}</pre>
        </n-ellipsis>
      );
    }
  },
  {
    key: 'time',
    title: '信息',
    width: 230,
    render: row => {
      return (
        <>
          <p>IP：{row.ip}</p>
          <p>开始时间：{row.beginTime > 0 ? new Date(row.beginTime * 1000).toLocaleString() : ''}</p>
          <p>完成时间：{row.completeTime > 0 ? new Date(row.completeTime * 1000).toLocaleString() : ''}</p>
        </>
      );
    }
  }
]) as Ref<DataTableColumns<Chat.Message>>;

async function init() {
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
