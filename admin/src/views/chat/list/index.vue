<template>
  <div class="overflow-hidden">
    <n-card title="AI聊天管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="类型">
              <n-select
                v-model:value="listParams.type"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.SessionType ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item>
              <n-input v-model:value="listParams.memberSearch" clearable placeholder="ID/用户搜索" />
            </n-form-item>
            <n-form-item>
              <n-button type="primary" @click="getTableData">
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
import { EyeOutline, SearchSharp, TrashOutline } from '@vicons/ionicons5';
import { deleteChatSession, fetchChatSessionList } from '@/service';
import { useLoading } from '@/hooks';
import { parseEnumWithAll, useAdminEnums } from '~/src/store';
import { useRouterPush } from '~/src/composables';
import { defaultPaginationProps } from '~/src/utils';

const { routerPush } = useRouterPush();
const { loading, startLoading, endLoading } = useLoading(false);

const enums = ref<any>({});
const listParams = ref({
  type: 0,
  memberSearch: ''
});

const tableData = ref<Chat.Session[]>([]);

const pagination = defaultPaginationProps(getTableData);

function setTableData(response: Chat.SessionListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchChatSessionList(
      listParams.value.memberSearch,
      listParams.value.type,
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

const columns: Ref<DataTableColumns<Chat.Session>> = ref([
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
    key: 'typeText',
    title: '类型',
    width: 100
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
        </>
      );
    }
  },
  {
    key: 'title',
    title: '内容',
    width: 240,
    render: row => {
      return (
        <>
          <p>标题：{row.title}</p>
          <p>提示语：{row.prompt}</p>
        </>
      );
    }
  },
  {
    key: 'Tokens',
    title: 'Tokens',
    width: 140,
    render: row => {
      return (
        <>
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
          <p>创建时间：{row.createTime > 0 ? new Date(row.createTime * 1000).toLocaleString() : ''}</p>
          <p>更新时间：{row.updateTime > 0 ? new Date(row.updateTime * 1000).toLocaleString() : ''}</p>
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
          <n-button type="primary" size={'small'} onClick={() => handleViewMessages(row.id)}>
            <n-icon component={EyeOutline} size="18" />
            查看
          </n-button>
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
]) as Ref<DataTableColumns<Chat.Session>>;

async function handleDeleteTable(rowId: number) {
  const { data } = await deleteChatSession(rowId);
  if (data?.code === 0) {
    window.$message?.info('删除成功');
    getTableData();
  }
}

function handleViewMessages(rowId: number) {
  routerPush({ name: 'chat_message_list', query: { sessionId: rowId } });
}

async function init() {
  enums.value = await useAdminEnums(['SessionType']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
