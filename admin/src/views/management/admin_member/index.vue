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
                :options="enums.AdminMemberStatus"
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
        <n-space class="py-12px" justify="space-between">
          <n-space>
            <n-button type="primary" @click="handleAddTable">
              <icon-ic-round-plus class="mr-4px text-20px" />
              新增
            </n-button>
          </n-space>
        </n-space>
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="row => row.id"
          flex-height
          class="flex-1-hidden"
        />
        <table-action-modal v-model:visible="visible" :type="modalType" :edit-data="editData" :enums="enums" />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { reactive, ref, watch } from 'vue';
import type { Ref } from 'vue';
import { NButton, NPopconfirm, NSpace, NTag } from 'naive-ui';
import type { DataTableColumns, PaginationProps } from 'naive-ui';
import { CreateOutline, SearchSharp, TrashOutline } from '@vicons/ionicons5';
import { deleteAdminMember, fetchAdminMemberList } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import { useEnums } from '~/src/store';
import TableActionModal from './components/table-action-modal.vue';
import type { ModalType } from './components/table-action-modal.vue';

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

const tableData = ref<UserManagement.User[]>([]);
function setTableData(response: Admin.AdminMemberListResponse) {
  tableData.value = response.list;
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
  const { data } = await fetchAdminMemberList(
    listParams.value.search,
    listParams.value.status,
    pagination.page,
    pagination.pageSize
  );
  if (data) {
    try {
      setTableData(data);
    } finally {
      endLoading();
    }
  }
}

const columns: Ref<DataTableColumns<UserManagement.User>> = ref([
  {
    key: 'id',
    title: 'ID',
    align: 'center'
  },
  {
    key: 'account',
    title: '用户名',
    align: 'center'
  },
  {
    key: 'nickname',
    title: '昵称',
    align: 'center'
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

        return <NTag type={tagTypes[row.status] ?? 'success'}>{row.statusText}</NTag>;
      }
      return <span></span>;
    }
  },
  {
    key: 'createTime',
    title: '创建时间',
    align: 'center',
    render: row => {
      return new Date(row.createTime * 1000).toLocaleString();
    }
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
          <NPopconfirm onPositiveClick={() => handleDeleteTable(row.id)}>
            {{
              default: () => '确认删除',
              trigger: () => (
                <NButton type="error" size={'small'}>
                  <n-icon component={TrashOutline} />
                  删除
                </NButton>
              )
            }}
          </NPopconfirm>
        </NSpace>
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
  enums.value = await useEnums(['AdminMemberStatus'], true);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
