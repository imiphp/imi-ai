<template>
  <div class="overflow-hidden">
    <n-card title="卡类型管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="状态">
              <n-select
                v-model:value="listParams.enable"
                class="!w-[140px]"
                :options="[
                  {
                    text: '全部',
                    value: undefined
                  },
                  {
                    text: '启用',
                    value: 1
                  },
                  {
                    text: '禁用',
                    value: 0
                  }
                ]"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item>
              <n-button attr-type="submit" type="primary" @click="getTableData">
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
          :row-key="row => row.id"
          scroll-x="1600"
          flex-height
          remote
          class="flex-1-hidden"
        />
        <edit-type-modal v-model:visible="visible" :type="modalType" :edit-data="editData" />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useDialog } from 'naive-ui';
import type { DataTableColumns } from 'naive-ui';
import { CreateOutline, SearchSharp, WalletOutline } from '@vicons/ionicons5';
import { fetchCardTypeList, updateCardType } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import { defaultPaginationProps, timespanHuman } from '~/src/utils';
import { useRouterPush } from '~/src/composables';
import EditTypeModal from './components/edit-type-modal.vue';
import type { ModalType } from './components/edit-type-modal.vue';

const { routerPush } = useRouterPush();
const dialog = useDialog();
const { loading, startLoading, endLoading } = useLoading(false);
const { bool: visible, setTrue: openModal } = useBoolean();
watch(visible, () => {
  if (!visible.value) {
    getTableData();
  }
});

const listParams = ref({
  enable: 1
});

const pagination = defaultPaginationProps(getTableData);

const tableData = ref<Card.CardType[]>([]);
function setTableData(response: Card.CardTypeListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchCardTypeList(
      undefined === listParams.value.enable ? undefined : Boolean(listParams.value.enable),
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

const columns: Ref<DataTableColumns<Card.CardType>> = ref([
  {
    key: 'id',
    title: 'ID'
  },
  {
    key: 'name',
    title: '名称'
  },
  {
    key: 'amount',
    title: '初始余额'
  },
  {
    key: 'expireSeconds',
    title: '有效期',
    align: 'center',
    render: row => {
      if (row.expireSeconds > 0) {
        return <>{timespanHuman(row.expireSeconds)}</>;
      }

      return <n-tag type="success">永久</n-tag>;
    }
  },
  {
    key: 'enable',
    title: '状态',
    width: 220,
    render: row => {
      return (
        <n-switch
          value={row.enable}
          on-update:value={async (value: boolean) => {
            dialog.warning({
              title: '询问',
              content: `是否${value ? '启用' : '禁用'}该卡类型？`,
              positiveText: '确定',
              negativeText: '取消',
              onPositiveClick: async () => {
                const { data } = await updateCardType(row.id, { enable: value });
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
        />
      );
    }
  },
  {
    key: 'system',
    title: '系统内置',
    width: 220,
    render: row => {
      return (
        <n-popover
          placement="top"
          trigger="click"
          duration={1000}
          v-slots={{
            trigger: () => (
              <n-switch
                value={row.system}
                v-slots={{
                  checked: () => '是',
                  unchecked: () => '否'
                }}
              />
            )
          }}
        >
          禁止修改
        </n-popover>
      );
    }
  },
  {
    key: 'createTime',
    title: '创建时间',
    width: 220,
    render: row => {
      return <>{row.createTime > 0 ? new Date(row.createTime * 1000).toLocaleString() : ''}</>;
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
          <n-button type="primary" size={'small'} onClick={() => handleCardList(row.id)}>
            <n-icon component={WalletOutline} size="18" />
            卡包
          </n-button>
        </n-space>
      );
    }
  }
]) as Ref<DataTableColumns<Card.CardType>>;

const modalType = ref<ModalType>('add');

function setModalType(type: ModalType) {
  modalType.value = type;
}

const editData = ref<Card.CardType | null>(null);

function setEditData(data: Card.CardType | null) {
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

function handleCardList(rowId: number) {
  routerPush({ name: 'card_list', query: { type: rowId } });
}

async function init() {
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
