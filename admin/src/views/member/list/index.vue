<template>
  <div class="overflow-hidden">
    <n-card title="用户管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="状态">
              <n-select
                v-model:value="listParams.status"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.MemberStatus ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item>
              <n-input v-model:value="listParams.search" clearable placeholder="关键词搜索" />
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
          flex-height
          class="flex-1-hidden"
        />
        <edit-member-modal v-if="editData" v-model:visible="visible" :edit-data="editData" :enums="enums" />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { reactive, ref, watch } from 'vue';
import type { Ref } from 'vue';
import { NButton, NSpace, useDialog } from 'naive-ui';
import type { DataTableColumns, PaginationProps } from 'naive-ui';
import { CreateOutline, SearchSharp } from '@vicons/ionicons5';
import { fetchCardMemberInfos, fetchMemberList, updateMember } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import { useEnums, parseEnumWithAll } from '~/src/store';
import EditMemberModal from './components/edit-member-modal.vue';

const dialog = useDialog();
const { loading, startLoading, endLoading } = useLoading(false);
const { bool: visible, setTrue: openModal } = useBoolean();
watch(visible, () => {
  if (!visible.value) {
    getTableData();
  }
});

const enums = ref<any>({});
const listParams = ref({
  status: 0,
  search: ''
});

const cardMemberInfos = ref<Card.CardMemberInfosResponse | undefined>();
const tableData = ref<Member.Member[]>([]);
function setTableData(response: Member.MemberListResponse) {
  tableData.value = response.list;
  getCardMemberInfos();
}

const pagination: PaginationProps = reactive({
  page: 1,
  pageSize: 10,
  showSizePicker: true,
  pageSizes: [10, 15, 20, 25, 30],
  onChange: (page: number) => {
    pagination.page = page;
    getTableData();
  },
  onUpdatePageSize: (pageSize: number) => {
    pagination.pageSize = pageSize;
    pagination.page = 1;
    getTableData();
  }
});

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchMemberList(
      listParams.value.search,
      listParams.value.status,
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

async function getCardMemberInfos() {
  const memberIds = [];
  for (const item of tableData.value) {
    memberIds.push(item.id);
  }
  const { data } = await fetchCardMemberInfos(memberIds);
  if (data) {
    cardMemberInfos.value = data;
  }
}

const columns: Ref<DataTableColumns<Member.Member>> = ref([
  {
    key: 'id',
    title: 'ID',
    align: 'center',
    render: row => {
      return `${row.recordId}（${row.id}）`;
    }
  },
  {
    key: 'nickname',
    title: '昵称',
    align: 'center'
  },
  {
    key: 'email',
    title: '邮箱',
    align: 'center'
  },
  {
    key: 'balance',
    title: '余额',
    align: 'center',
    render: row => {
      return (
        <span title={cardMemberInfos.value?.data[row.id]?.balance ?? ''}>
          {cardMemberInfos.value?.data[row.id]?.balanceText ?? '加载中...'}
        </span>
      );
    }
  },
  {
    key: 'status',
    title: '状态',
    align: 'center',
    render: row => {
      return (
        <n-switch
          value={row.status === 1}
          on-update:value={async (value: boolean) => {
            dialog.warning({
              title: '询问',
              content: `是否${value ? '启用' : '禁用'}该用户？`,
              positiveText: '确定',
              negativeText: '取消',
              onPositiveClick: async () => {
                const { data } = await updateMember(row.id, { status: value ? 1 : 2 });
                if (data?.code === 0) {
                  getTableData();
                }
              }
            });
          }}
          v-slots={{
            checked: () => '启用',
            unchecked: () => '禁用'
          }}
        ></n-switch>
      );
    }
  },
  {
    key: 'registerTime',
    title: '注册时间',
    align: 'center',
    render: row => {
      return new Date(row.registerTime * 1000).toLocaleString();
    }
  },
  {
    key: 'registerIp',
    title: '注册IP',
    align: 'center'
  },
  {
    key: 'lastLoginTime',
    title: '最后登录时间',
    align: 'center',
    render: row => {
      return row.lastLoginTime > 0 ? new Date(row.lastLoginTime * 1000).toLocaleString() : '';
    }
  },
  {
    key: 'lastLoginIp',
    title: '最后登录IP',
    align: 'center'
  },
  {
    key: 'actions',
    title: '操作',
    align: 'center',
    render: row => {
      return (
        <NSpace justify={'center'}>
          <NButton size={'small'} onClick={() => handleEditTable(row.id)}>
            <n-icon component={CreateOutline} />
            编辑
          </NButton>
        </NSpace>
      );
    }
  }
]) as Ref<DataTableColumns<Member.Member>>;

const editData = ref<Member.Member | null>(null);

function setEditData(data: Member.Member | null) {
  editData.value = data;
}

function handleEditTable(rowId: number) {
  const findItem = tableData.value.find(item => item.id === rowId);
  if (findItem) {
    const data = { ...findItem };
    data.password = '';
    setEditData(data);
  }
  openModal();
}

async function init() {
  enums.value = await useEnums(['MemberStatus']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
