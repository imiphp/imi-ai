<script setup lang='tsx'>
import { NButton, NDataTable, NIcon, NProgress, NSpace, NSwitch } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'
import { h, onMounted, reactive, ref } from 'vue'
import { List } from '@vicons/ionicons5'
import { CardDetails } from '.'
import { cardList } from '@/api'

interface Props {
  expired?: boolean
}
const props = defineProps<Props>()

const detailId = ref('')
const showDetail = ref(false)

const createColumns = ({
  details,
}: {
  details: (row: any) => void
}): DataTableColumns<Card.Card> => {
  return [
    {
      title: '卡号',
      key: 'recordId',
    },
    {
      title: '名称',
      key: 'cardType.name',
    },
    {
      title: '余额/面额',
      key: 'amount',
      width: 250,
      render(row) {
        if (row.type === 1) {
          return row.leftAmountText
        }
        else {
          const percent = row.leftAmount / row.amount * 100
          let status = 'default'
          let color
          if (row.expired) {
            color = 'gray'
          }
          else {
            if (percent <= 60)
              status = 'warning'
            else if (percent <= 20)
              status = 'error'
          }

          return h(NProgress, {
            indicatorPlacement: 'inside',
            color,
            percentage: parseFloat(percent.toFixed(2)),
            status,
          } as any, {
            default: () => `${row.leftAmountText}/${row.amountText}`,
          })
        }
      },
    },
    {
      title: '付费标志',
      key: 'paying',
      width: 100,
      render(row) {
        return (
        <NSwitch
          value={row.paying}
          v-slots={{
            checked: () => '付费',
            unchecked: () => '免费',
          }}
        />
        )
      },
    },
    {
      key: 'enable',
      title: '状态',
      width: 100,
      render: (row) => {
        return (
        <NSwitch
          value={row.enable}
          v-slots={{
            checked: () => '启用',
            unchecked: () => '禁用',
          }}
        />
        )
      },
    },
    {
      title: '激活时间',
      key: 'activationTime',
      render(row) {
        return new Date(row.activationTime * 1000).toLocaleString()
      },
    },
    {
      title: '过期时间',
      key: 'expireTime',
      render(row) {
        return row.expireTime > 0 ? (new Date(row.expireTime * 1000).toLocaleString() + (row.expired ? '（已过期）' : '')) : '永久有效'
      },
    },
    {
      title: '操作',
      key: 'actions',
      render(row) {
        return h(
          NSpace,
          null,
          {
            default: () => [
              h(
                NButton,
                {
                  strong: true,
                  size: 'small',
                  type: 'info',
                  renderIcon: () => h(NIcon, null, { default: () => h(List) }),
                  onClick: () => details(row),
                },
                { default: () => '明细' },
              ),
            ],
          },
        )
      },
    },
  ]
}

const columns = createColumns({
  details(row) {
    detailId.value = row.recordId
    showDetail.value = true
  },
})
const tableLoading = ref(false)
const data = ref<Card.Card[]>([])
const pagination = reactive({
  page: 1,
  pageSize: 15,
  pageCount: 1,
  itemCount: 0,
  onChange: (page: number) => {
    pagination.page = page
    loadList()
  },
  onUpdatePageSize: (pageSize: number) => {
    pagination.pageSize = pageSize
    pagination.page = 1
  },
})

async function loadList() {
  tableLoading.value = true
  try {
    const response = await cardList(props.expired, pagination.page, pagination.pageSize)
    data.value = response.list
    pagination.pageCount = response.pageCount
    pagination.itemCount = response.total
  }
  finally {
    tableLoading.value = false
  }
}

onMounted(async () => {
  await loadList()
})
</script>

<template>
  <CardDetails v-if="showDetail" v-model:visible="showDetail" :card-id="detailId" />
  <NDataTable
    v-show="!showDetail"
    :columns="columns"
    :data="data"
    :bordered="false"
    :loading="tableLoading"
    scroll-x="max-content"
    remote
    :pagination="pagination"
  />
</template>
