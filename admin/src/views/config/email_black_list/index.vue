<template>
  <div class="overflow-hidden">
    <n-card title="邮箱域名黑名单" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
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
              增加
            </n-button>
            <n-button type="error" @click="handleBatchDelete">
              <n-icon :component="TrashOutline" size="20" />
              批量删除
            </n-button>
          </n-space>
        </n-space>
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="row => row.domain"
          scroll-x="1024"
          flex-height
          remote
          class="flex-1-hidden"
          @update:checked-row-keys="handleCheck"
        />
      </div>
    </n-card>
    <add-domains-modal v-if="showAddDomainsModal" v-model:visible="showAddDomainsModal" />
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { DataTableColumns, DataTableRowKey } from 'naive-ui';
import { SearchSharp, TrashOutline } from '@vicons/ionicons5';
import { fetchEmailBlackList, removeEmailBlackList } from '@/service';
import { useLoading } from '@/hooks';
import { defaultPaginationProps } from '~/src/utils';
import AddDomainsModal from './components/add-domains-modal.vue';

interface Row {
  domain: string;
}

const { loading, startLoading, endLoading } = useLoading(false);

const listParams = ref({
  search: ''
});

const showAddDomainsModal = ref(false);
watch(showAddDomainsModal, () => {
  if (!showAddDomainsModal.value) {
    getTableData();
  }
});

const pagination = defaultPaginationProps(getTableData, true);
delete pagination.itemCount;

const tableData = ref<Row[]>([]);
function setTableData(response: Email.EmailBlackListResponse) {
  tableData.value = response.list.map((item: string) => {
    return {
      domain: item
    };
  });
  if (tableData.value.length > 0) {
    pagination.pageCount = pagination.page + 1;
  }
}

async function getTableData(page: number | null = null) {
  startLoading();
  if (page !== null) {
    pagination.page = page;
  }
  try {
    const { data } = await fetchEmailBlackList(listParams.value.search, pagination.page, pagination.pageSize);
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const showEditRemarkModal = ref(false);
watch(showEditRemarkModal, () => {
  if (!showEditRemarkModal.value) {
    getTableData();
  }
});

const columns: Ref<DataTableColumns<Row>> = ref([
  {
    type: 'selection'
  },
  {
    title: '域名',
    key: 'domain',
    render: row => {
      return row.domain;
    }
  },
  {
    key: 'actions',
    title: '操作',
    width: 200,
    render: row => {
      return (
        <n-space justify={'center'}>
          <n-popconfirm onPositiveClick={() => handleDeleteTable(row)}>
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
]) as Ref<DataTableColumns<Row>>;

const checkedRowKeysRef = ref<DataTableRowKey[]>([]);

function handleCheck(rowKeys: DataTableRowKey[]) {
  checkedRowKeysRef.value = rowKeys;
}

function handleAddTable() {
  showAddDomainsModal.value = true;
}

async function handleDeleteTable(row: Row) {
  try {
    loading.value = true;
    const { data } = await removeEmailBlackList([row.domain]);
    if (data?.code === 0) {
      window.$message?.info('删除成功');
      getTableData();
    }
  } finally {
    loading.value = false;
  }
}

async function handleBatchDelete() {
  try {
    loading.value = true;
    const domains: string[] = [];
    checkedRowKeysRef.value.forEach(item => {
      domains.push(item.toString());
    });
    const { data } = await removeEmailBlackList(domains);
    if (data?.code === 0) {
      window.$message?.info('删除成功');
      getTableData();
    }
  } finally {
    loading.value = false;
  }
}

async function init() {
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
