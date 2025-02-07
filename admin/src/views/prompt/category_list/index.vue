<template>
  <div class="overflow-hidden">
    <n-card title="提示语分类管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
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
          :row-key="(row: any) => row.id"
          flex-height
          remote
          class="flex-1-hidden"
        />
        <edit-modal v-model:visible="visible" :type="modalType" :edit-data="editData" />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { DataTableColumns } from 'naive-ui';
import { CreateOutline, TrashOutline } from '@vicons/ionicons5';
import { deletePromptCategory, fetchPromptCategoryList } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import EditModal from './components/edit-modal.vue';
import type { ModalType } from './components/edit-modal.vue';

const { loading, startLoading, endLoading } = useLoading(false);
const { bool: visible, setTrue: openModal } = useBoolean();
watch(visible, () => {
  if (!visible.value) {
    getTableData();
  }
});

const tableData = ref<Prompt.PromptCategory[]>([]);
function setTableData(response: Prompt.PromptCategoryListResponse) {
  tableData.value = response.list;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchPromptCategoryList();
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const columns: Ref<DataTableColumns<Prompt.PromptCategory>> = ref([
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
    key: 'title',
    title: '标题',
    width: 200
  },
  {
    key: 'index',
    title: '排序',
    width: 60
  },
  {
    key: 'createTime',
    title: '时间',
    width: 220,
    render: row => {
      return (
        <>
          <p>创建时间：{row.createTime > 0 ? new Date(row.createTime * 1000).toLocaleString() : ''}</p>
          <p>更新时间：{row.updateTime > 0 ? new Date(row.updateTime * 1000).toLocaleString() : ''}</p>
        </>
      );
    }
  },
  {
    key: 'actions',
    title: '操作',
    width: 80,
    render: row => {
      return (
        <n-space>
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
]) as Ref<DataTableColumns<Prompt.PromptCategory>>;

const modalType = ref<ModalType>('add');

function setModalType(type: ModalType) {
  modalType.value = type;
}

const editData = ref<Prompt.PromptCategory | null>(null);

function setEditData(data: Prompt.PromptCategory | null) {
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
  const { data } = await deletePromptCategory(rowId);
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
