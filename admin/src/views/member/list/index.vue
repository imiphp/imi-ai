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
          scroll-x="1600"
          flex-height
          remote
          class="flex-1-hidden"
        />
        <edit-member-modal v-model:visible="visible" :edit-data="editData" :enums="enums" />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useDialog } from 'naive-ui';
import type { DataTableColumns } from 'naive-ui';
import { CreateOutline, SearchSharp, WalletOutline, List } from '@vicons/ionicons5';
import { fetchCardMemberInfos, fetchMemberList, updateMember } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import { useAdminEnums, parseEnumWithAll } from '~/src/store';
import { defaultPaginationProps } from '~/src/utils';
import { useRouterPush } from '~/src/composables';
import EditMemberModal from './components/edit-member-modal.vue';

const { routerPush } = useRouterPush();
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

const pagination = defaultPaginationProps(getTableData);

const cardMemberInfos = ref<Card.CardMemberInfosResponse | undefined>();
const tableData = ref<Member.Member[]>([]);
function setTableData(response: Member.MemberListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
  getCardMemberInfos();
}

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
    key: 'nickname',
    title: '昵称',
    width: 180
  },
  {
    key: 'email',
    title: '邮箱',
    width: 180
  },
  {
    key: 'balance',
    title: '余额',
    width: 100,
    render: row => {
      return (
        <span title={cardMemberInfos.value?.data[row.id]?.balance.toString() ?? ''}>
          {cardMemberInfos.value?.data[row.id]?.balanceText ?? '加载中...'}
        </span>
      );
    }
  },
  {
    key: 'status',
    title: '状态',
    width: 100,
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
    key: 'registerInfo',
    title: '注册信息',
    width: 220,
    render: row => {
      return (
        <>
          <p>注册时间：{new Date(row.registerTime * 1000).toLocaleString()}</p>
          <p>注册IP：{row.registerIp}</p>
        </>
      );
    }
  },
  {
    key: 'lastLoginInfo',
    title: '最后登录',
    width: 220,
    render: row => {
      return (
        <>
          <p>最后登录时间：{row.lastLoginTime > 0 ? new Date(row.lastLoginTime * 1000).toLocaleString() : ''}</p>
          <p>最后登录IP：{row.lastLoginIp}</p>
        </>
      );
    }
  },
  {
    key: 'actions',
    title: '操作',
    width: 160,
    render: row => {
      return (
        <n-space>
          <n-button size={'small'} onClick={() => handleEditTable(row.id)}>
            <n-icon component={CreateOutline} size="18" />
            编辑
          </n-button>
          <n-button type="success" size={'small'} onClick={() => handleMemberCardDetail(row.id)}>
            <n-icon component={List} size="18" />
            明细
          </n-button>
          <n-button type="primary" size={'small'} onClick={() => handleMemberCardList(row.id)}>
            <n-icon component={WalletOutline} size="18" />
            卡包
          </n-button>
        </n-space>
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

function handleMemberCardDetail(rowId: number) {
  const findItem = tableData.value.find(item => item.id === rowId);
  if (findItem) {
    routerPush({ name: 'card_memberCardDetails', query: { memberId: rowId } });
  }
}

function handleMemberCardList(rowId: number) {
  const findItem = tableData.value.find(item => item.id === rowId);
  if (findItem) {
    routerPush({ name: 'card_list', query: { memberId: rowId } });
  }
}

async function init() {
  enums.value = await useAdminEnums(['MemberStatus']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
