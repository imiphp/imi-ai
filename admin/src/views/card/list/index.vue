<template>
  <div class="overflow-hidden">
    <n-card title="卡包列表" :bordered="false" class="h-full rounded-8px shadow-sm">
      <div class="flex-col h-full">
        <n-form label-placement="left" :show-feedback="false" class="pb-2.5">
          <n-space class="flex flex-row flex-wrap">
            <n-form-item label="过期">
              <n-select
                v-model:value="listParams.expired"
                class="!w-[140px]"
                :options="[
                  {
                    text: '全部',
                    value: -1
                  },
                  {
                    text: '可用',
                    value: 0
                  },
                  {
                    text: '已过期',
                    value: 1
                  }
                ]"
                label-field="text"
                value-field="value"
              />
            </n-form-item>
            <n-form-item label="激活">
              <n-select
                v-model:value="listParams.activationed"
                class="!w-[140px]"
                :options="[
                  {
                    text: '全部',
                    value: -1
                  },
                  {
                    text: '未激活',
                    value: 0
                  },
                  {
                    text: '已激活',
                    value: 1
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
          <n-button v-if="listParams.type > 0" type="primary" @click="handleGenerate">
            <icon-ic-round-plus class="mr-4px text-20px" />
            批量生成
          </n-button>
        </n-space>
        <n-data-table
          :columns="columns"
          :data="tableData"
          :loading="loading"
          :pagination="pagination"
          :row-key="row => row.id"
          scroll-x="1024"
          flex-height
          remote
          class="flex-1-hidden"
        />
      </div>
    </n-card>
    <generate-card-modal
      v-if="listParams.type > 0"
      v-model:visible="showGenerateCardModal"
      :card-type="listParams.type"
    />
    <edit-remark-modal
      v-if="showEditRemarkModal"
      v-model:visible="showEditRemarkModal"
      :card-id="editRemarkCardId"
      :remark="editRemark"
    />
  </div>
</template>

<script setup lang="tsx">
import { ref, watch } from 'vue';
import type { Ref } from 'vue';
import { useRoute } from 'vue-router';
import type { DataTableColumns } from 'naive-ui';
import { CreateOutline, List, SearchSharp } from '@vicons/ionicons5';
import { fetchCardList } from '@/service';
import { useLoading } from '@/hooks';
import { useEnums } from '~/src/store';
import { defaultPaginationProps } from '~/src/utils';
import { useRouterPush } from '~/src/composables';
import GenerateCardModal from './components/generate-card-modal.vue';
import EditRemarkModal from './components/edit-remark-modal.vue';

const { routerPush } = useRouterPush();
const route = useRoute();
const { loading, startLoading, endLoading } = useLoading(false);

const enums = ref<any>({});
const listParams = ref({
  memberId: parseInt(route.query.memberId?.toString() ?? '0'),
  expired: 0,
  activationed: -1,
  type: parseInt(route.query.type?.toString() ?? '0')
});

const showGenerateCardModal = ref(false);
watch(showGenerateCardModal, () => {
  if (!showGenerateCardModal.value) {
    getTableData();
  }
});

const pagination = defaultPaginationProps(getTableData);

const tableData = ref<Card.Card[]>([]);
function setTableData(response: Card.CardListResponse) {
  tableData.value = response.list;
  pagination.pageCount = response.pageCount;
  pagination.itemCount = response.total;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchCardList(
      listParams.value.memberId,
      listParams.value.type,
      listParams.value.activationed >= 0 ? Boolean(listParams.value.activationed) : undefined,
      listParams.value.expired >= 0 ? Boolean(listParams.value.expired) : undefined,
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

const showEditRemarkModal = ref(false);
watch(showEditRemarkModal, () => {
  if (!showEditRemarkModal.value) {
    getTableData();
  }
});
const editRemarkCardId = ref(0);
const editRemark = ref('');

const columns: Ref<DataTableColumns<Card.Card>> = ref([
  {
    title: '卡号',
    key: 'recordId',
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
      if (row.memberInfo)
        return (
          <>
            <p>
              ID：{row.memberId} ({row.memberInfo.recordId})
            </p>
            <p>昵称：{row.memberInfo.nickname}</p>
          </>
        );
      return '';
    }
  },
  {
    title: '名称',
    key: 'cardType.name',
    width: 100
  },
  {
    title: '余额/面额',
    key: 'amount',
    minWidth: 300,
    render(row) {
      if (row.type === 1) {
        return row.leftAmountText;
      }

      const percent = (row.leftAmount / row.amount) * 100;
      let status = 'default';
      let color;
      if (row.expired) {
        color = 'gray';
      } else if (percent <= 60) status = 'warning';
      else if (percent <= 20) status = 'error';

      return (
        <n-progress
          color={color}
          /* naive-ui bug，下个版本修复 */
          /* indicatorPlacement="inside" */
          percentage={parseFloat(percent.toFixed(2))}
          status={status}
        >
          {{
            default: () => `${row.leftAmountText}/${row.amountText}`
          }}
        </n-progress>
      );
    }
  },
  {
    title: '创建时间',
    key: 'activationTime',
    width: 200,
    render(row) {
      return new Date(row.createTime * 1000).toLocaleString();
    }
  },
  {
    title: '激活时间',
    key: 'activationTime',
    width: 200,
    render(row) {
      if (row.activationTime > 0) return new Date(row.activationTime * 1000).toLocaleString();
      return '未激活';
    }
  },
  {
    title: '过期时间',
    key: 'expireTime',
    width: 250,
    render(row) {
      return row.expireTime > 0
        ? new Date(row.expireTime * 1000).toLocaleString() + (row.expired ? '（已过期）' : '')
        : '永久有效';
    }
  },
  {
    title: '备注',
    key: 'ex.adminRemark',
    render(row) {
      return (
        <>
          <n-ellipsis expand-trigger="click" line-clamp="5" tooltip={false}>
            <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{row.ex?.adminRemark}</pre>
          </n-ellipsis>
          <n-button size={'small'} bordered={false} class="align-super" onClick={() => handleUpdateRemark(row)}>
            <n-icon component={CreateOutline} size="18" />
          </n-button>
        </>
      );
    }
  },
  {
    key: 'actions',
    title: '操作',
    width: 100,
    render: row => {
      return (
        <n-space>
          <n-button type="success" size={'small'} onClick={() => handleCardDetail(row.id)}>
            <n-icon component={List} size="18" />
            明细
          </n-button>
        </n-space>
      );
    }
  }
]) as Ref<DataTableColumns<Card.Card>>;

function handleCardDetail(rowId: number) {
  const findItem = tableData.value.find(item => item.id === rowId);
  if (findItem) {
    routerPush({ name: 'card_details', query: { cardId: rowId } });
  }
}

function handleGenerate() {
  showGenerateCardModal.value = true;
}

function handleUpdateRemark(card: Card.Card) {
  editRemarkCardId.value = card.id;
  editRemark.value = card.ex?.adminRemark ?? '';
  showEditRemarkModal.value = true;
}

async function init() {
  enums.value = await useEnums(['BusinessType', 'OperationType']);
  getTableData();
}

// 初始化
init();
</script>

<style scoped></style>
