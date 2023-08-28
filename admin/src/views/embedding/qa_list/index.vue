<template>
  <div class="overflow-hidden">
    <n-card title="模型对话管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="row => row.id"
          scroll-x="1600"
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
import { TrashOutline } from '@vicons/ionicons5';
import { deleteEmbeddingChat, fetchEmbeddingQAList } from '@/service';
import { useLoading } from '@/hooks';
import { defaultPaginationProps } from '~/src/utils';

const route = useRoute();
const projectId = parseInt(route.query.projectId?.toString() ?? '0');
const { loading, startLoading, endLoading } = useLoading(false);

const tableData = ref<Embedding.QA[]>([]);

const pagination = defaultPaginationProps(getTableData);

function setTableData(response: Embedding.QAListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchEmbeddingQAList(projectId, pagination.page, pagination.pageSize);
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const columns: Ref<DataTableColumns<Embedding.QA>> = ref([
  {
    key: 'id',
    title: '基本信息',
    width: 160,
    render: row => {
      return (
        <>
          <p>ID：{row.id}</p>
          <p>加密ID：{row.recordId}</p>
          <p>状态：{row.statusText}</p>
        </>
      );
    }
  },
  {
    key: 'question',
    title: '问题',
    width: 300,
    render: row => {
      return <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.question}</pre>;
    }
  },
  {
    key: 'answer',
    title: '回答',
    width: 450,
    render: row => {
      return (
        <n-ellipsis expand-trigger="click" line-clamp="5" tooltip={false}>
          <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.answer}</pre>
        </n-ellipsis>
      );
    }
  },
  {
    key: 'member',
    title: '用户',
    width: 140,
    render: row => {
      return (
        <>
          <p>
            ID：{row.memberId} ({row.memberInfo.recordId})
          </p>
          <p>昵称：{row.memberInfo.nickname}</p>
          <p>实际：{row.tokens}</p>
          <p>消耗：{row.payTokens}</p>
        </>
      );
    }
  },
  {
    key: 'time',
    title: '信息',
    width: 220,
    render: row => {
      return (
        <>
          <p>IP：{row.ip}</p>
          <p>创建时间：{row.createTime > 0 ? new Date(row.createTime).toLocaleString() : ''}</p>
          <p>开始时间：{row.beginTime > 0 ? new Date(row.beginTime).toLocaleString() : ''}</p>
          <p>完成时间：{row.completeTime > 0 ? new Date(row.completeTime).toLocaleString() : ''}</p>
        </>
      );
    }
  },
  {
    key: 'actions',
    title: '操作',
    align: 'center',
    width: 100,
    render: row => {
      return (
        <n-space justify={'center'}>
          <n-popconfirm onPositiveClick={() => handleDeleteTable(row.id)}>
            {{
              default: () => '确认删除',
              trigger: () => (
                <n-button type="error" size={'small'}>
                  <n-icon component={TrashOutline} size="18" />
                  删除
                </n-button>
              )
            }}
          </n-popconfirm>
        </n-space>
      );
    }
  }
]) as Ref<DataTableColumns<Embedding.QA>>;

async function handleDeleteTable(rowId: number) {
  const { data } = await deleteEmbeddingChat(rowId);
  if (data?.code === 0) {
    window.$message?.info('删除成功');
    getTableData();
  }
}

async function init() {
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
