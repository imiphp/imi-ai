<template>
  <div class="overflow-hidden">
    <n-card title="后台用户管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="状态">
              <n-select
                v-model:value="listParams.status"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.AdminMemberStatus ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item>
              <n-input v-model:value="listParams.search" clearable placeholder="关键词搜索" />
            </n-form-item>
            <n-form-item>
              <n-button attr-type="submit" type="primary" @click="getTableData(1)">
                <n-icon :component="SearchSharp" size="20" />
                查询
              </n-button>
            </n-form-item>
          </n-space>
        </n-form>
        <n-space class="py-12px" justify="space-between">
          <n-space>
            <n-button type="primary" @click="handleAddTable">
              <icon-ic-round-plus class="mr-4px text-20px" />
              创建
            </n-button>
          </n-space>
        </n-space>
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="(row: any) => row.id"
          scroll-x="1280"
          flex-height
          remote
          class="flex-1-hidden"
        />
        <edit-admin-member-modal v-model:visible="visible" :type="modalType" :edit-data="editData" :enums="enums" />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { DataTableColumns } from 'naive-ui';
import { CreateOutline, SearchSharp, TrashOutline } from '@vicons/ionicons5';
import { deleteAdminMember, fetchAdminMemberList } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import { useAdminEnums, parseEnumWithAll } from '~/src/store';
import { defaultPaginationProps } from '~/src/utils';
import EditAdminMemberModal from './components/edit-admin-member-modal.vue';
import type { ModalType } from './components/edit-admin-member-modal.vue';

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

const tableData = ref<UserManagement.User[]>([]);
function setTableData(response: Admin.AdminMemberListResponse) {
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
    const { data } = await fetchAdminMemberList(
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

const columns: Ref<DataTableColumns<UserManagement.User>> = ref([
  {
    key: 'id',
    title: 'ID'
  },
  {
    key: 'account',
    title: '用户名'
  },
  {
    key: 'nickname',
    title: '昵称'
  },
  {
    key: 'status',
    title: '状态',
    align: 'center',
    render: row => {
      if (row.status) {
        const tagTypes: Record<UserManagement.UserStatusKey, NaiveUI.ThemeColor> = {
          '1': 'success',
          '2': 'error'
        };

        return <n-tag type={tagTypes[row.status] ?? 'success'}>{row.statusText}</n-tag>;
      }
      return <span></span>;
    }
  },
  {
    key: 'createTime',
    title: '创建时间',
    render: row => {
      return new Date(row.createTime * 1000).toLocaleString();
    }
  },
  {
    key: 'lastLoginTime',
    title: '最后登录时间',
    render: row => {
      return row.lastLoginTime > 0 ? new Date(row.lastLoginTime * 1000).toLocaleString() : '';
    }
  },
  {
    key: 'lastLoginIp',
    title: '最后登录IP'
  },
  {
    key: 'actions',
    title: '操作',
    align: 'center',
    render: row => {
      return (
        <n-space justify={'center'}>
          <n-button size={'small'} onClick={() => handleEditTable(row.id)}>
            <n-icon component={CreateOutline} size="18" />
            编辑
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
]) as Ref<DataTableColumns<UserManagement.User>>;

const modalType = ref<ModalType>('add');

function setModalType(type: ModalType) {
  modalType.value = type;
}

const editData = ref<UserManagement.User | null>(null);

function setEditData(data: UserManagement.User | null) {
  editData.value = data;
}

function handleAddTable() {
  setModalType('add');
  openModal();
}

function handleEditTable(rowId: number) {
  const findItem = tableData.value.find(item => item.id === rowId);
  if (findItem) {
    setEditData({ ...findItem });
  }
  setModalType('edit');
  openModal();
}

async function handleDeleteTable(rowId: number) {
  const { data } = await deleteAdminMember(rowId);
  if (data?.code === 0) {
    window.$message?.info('删除成功');
    getTableData();
  }
}

async function init() {
  enums.value = await useAdminEnums(['AdminMemberStatus']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
