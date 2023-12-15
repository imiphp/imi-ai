<template>
  <div class="overflow-hidden">
    <n-card title="提示语管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="类型">
              <n-select
                v-model:value="listParams.type"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.PromptType ?? [])"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="分类">
              <n-select
                v-model:value="listParams.categoryIds"
                class="!w-[360px]"
                filterable
                tag
                multiple
                :options="promptCategorySelectOptions(categorys)"
                placeholder="留空不限制"
              ></n-select>
            </n-form-item>
            <n-form-item label="搜索">
              <n-input v-model:value="listParams.search" clearable placeholder="搜索内容" />
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
          :row-key="row => row.id"
          scroll-x="1600"
          flex-height
          remote
          class="flex-1-hidden"
        />
        <edit-modal
          v-model:visible="visible"
          :type="modalType"
          :edit-data="editData"
          :type-select-options="enums.PromptType ?? []"
          :category-select-options="promptCategorySelectOptions(categorys)"
          :config="config"
        />
      </div>
    </n-card>
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import type { DataTableColumns } from 'naive-ui';
import { CreateOutline, SearchSharp, TrashOutline } from '@vicons/ionicons5';
import { deletePrompt, fetchPromptCategoryList, fetchPromptList, getConfig } from '@/service';
import { useBoolean, useLoading } from '@/hooks';
import { promptCategorySelectOptions, useAdminEnums, parseEnumWithAll } from '~/src/store';
import { defaultPaginationProps } from '~/src/utils';
import EditModal from './components/edit-modal.vue';
import type { ModalType } from './components/edit-modal.vue';

const { loading, startLoading, endLoading } = useLoading(false);
const { bool: visible, setTrue: openModal } = useBoolean();
watch(visible, () => {
  if (!visible.value) {
    getTableData();
  }
});

const config = ref<any>({});
const enums = ref<any>({});
const categorys = ref<Prompt.PromptCategory[]>([]);
const listParams = ref({
  type: 0,
  categoryIds: [],
  search: ''
});

const pagination = defaultPaginationProps(getTableData);

const tableData = ref<Prompt.Prompt[]>([]);
function setTableData(response: Prompt.PromptListResponse) {
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
    const { data } = await fetchPromptList(
      listParams.value.type,
      listParams.value.categoryIds,
      listParams.value.search,
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

const columns: Ref<DataTableColumns<Prompt.Prompt>> = ref([
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
    width: 80
  },
  {
    key: 'title',
    title: '标题',
    width: 200
  },
  {
    key: 'description',
    title: '简介',
    width: 200,
    render(row) {
      return (
        <>
          <n-ellipsis expand-trigger="click" line-clamp="5" tooltip={false}>
            <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.description}</pre>
          </n-ellipsis>
        </>
      );
    }
  },
  {
    key: 'prompt',
    title: '提示语',
    width: 300,
    render(row) {
      return (
        <>
          <n-ellipsis expand-trigger="click" line-clamp="5" tooltip={false}>
            <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.prompt}</pre>
          </n-ellipsis>
        </>
      );
    }
  },
  {
    key: 'category',
    title: '分类',
    width: 100,
    render(row) {
      const tags: any[] = [];
      for (const title of row.categoryTitles) {
        tags.push(<n-tag>{title}</n-tag>);
      }
      return <n-space>{tags}</n-space>;
    }
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
]) as Ref<DataTableColumns<Prompt.Prompt>>;

const modalType = ref<ModalType>('add');

function setModalType(type: ModalType) {
  modalType.value = type;
}

const editData = ref<Prompt.Prompt | null>(null);

function setEditData(data: Prompt.Prompt | null) {
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
  const { data } = await deletePrompt(rowId);
  if (data?.code === 0) {
    window.$message?.info('删除成功');
    getTableData();
  }
}

async function loadCategorys() {
  const { data } = await fetchPromptCategoryList();
  if (data) {
    categorys.value = data.list;
  }
}

async function init() {
  await Promise.all([
    (async () => {
      enums.value = await useAdminEnums(['PromptType']);
    })(),
    (async () => {
      const { data } = await getConfig();
      config.value = (data as any).data;
    })(),
    loadCategorys()
  ]);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
