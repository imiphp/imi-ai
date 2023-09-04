<template>
  <div class="overflow-hidden">
    <n-card title="模型训练管理" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="状态">
              <n-select
                v-model:value="listParams.status"
                class="!w-[140px]"
                :options="parseEnumWithAll(enums.EmbeddingPublicProjectStatus ?? [])"
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
import { Checkmark, Close, SearchSharp, TrashOutline } from '@vicons/ionicons5';
import { deleteEmbeddingProject, fetchEmbeddingPublicProjectList, reviewEmbeddingPublicProject } from '@/service';
import { useLoading } from '@/hooks';
import { defaultPaginationProps } from '~/src/utils';
import { formatByte } from '~/src/utils/auth';
import { parseEnumWithAll, useAdminEnums } from '~/src/store';

const { loading, startLoading, endLoading } = useLoading(false);

const enums = ref<any>({});
const listParams = ref({
  status: 0
});

const tableData = ref<Embedding.PublicProject[]>([]);

const pagination = defaultPaginationProps(getTableData);

function setTableData(response: Embedding.PublicProjectListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchEmbeddingPublicProjectList(
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

const columns: Ref<DataTableColumns<Embedding.PublicProject>> = ref([
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
    key: 'name',
    title: '名称',
    width: 150
  },
  {
    key: 'statusText',
    title: '状态',
    width: 100,
    render(row) {
      return (
        <>
          <p>项目状态：{row.statusText}</p>
          <p>审核状态：{row.publicProject.statusText}</p>
        </>
      );
    }
  },
  {
    key: 'totalFileSize',
    title: '文件总大小',
    width: 100,
    render(row) {
      return formatByte(row.totalFileSize);
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
          <p>创建时间：{row.createTime > 0 ? new Date(row.createTime).toLocaleString() : ''}</p>
          <p>更新时间：{row.updateTime > 0 ? new Date(row.updateTime).toLocaleString() : ''}</p>
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
      let review;
      if (row.publicProject.status === 1) {
        review = (
          <n-popconfirm onPositiveClick={() => handleReview(row.id, false)}>
            {{
              default: () => '确认审核',
              trigger: () => {
                return (
                  <n-button type="error" size={'small'}>
                    <n-icon component={Close} size="18" />
                    关闭
                  </n-button>
                );
              }
            }}
          </n-popconfirm>
        );
      } else {
        review = (
          <n-popconfirm onPositiveClick={() => handleReview(row.id, true)}>
            {{
              default: () => '确认审核',
              trigger: () => {
                return (
                  <n-button type="success" size={'small'}>
                    <n-icon component={Checkmark} size="18" />
                    公开
                  </n-button>
                );
              }
            }}
          </n-popconfirm>
        );
      }
      return (
        <n-space justify={'center'}>
          {review}
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
]) as Ref<DataTableColumns<Embedding.PublicProject>>;

async function handleDeleteTable(rowId: number) {
  const { data } = await deleteEmbeddingProject(rowId);
  if (data?.code === 0) {
    window.$message?.info('删除成功');
    getTableData();
  }
}

async function handleReview(rowId: number, pass: boolean) {
  const { data } = await reviewEmbeddingPublicProject(rowId, pass);
  if (data?.code === 0) {
    window.$message?.info('审核成功');
    getTableData();
  }
}

async function init() {
  enums.value = await useAdminEnums(['EmbeddingPublicProjectStatus']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
