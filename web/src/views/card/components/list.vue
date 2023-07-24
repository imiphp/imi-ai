<script setup lang='ts'>
import { NDataTable, NProgress } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'
import { h, onMounted, reactive, ref } from 'vue'
import { cardList } from '@/api'

interface Props {
  expired?: boolean
}
const props = defineProps<Props>()

const createColumns = (): DataTableColumns<Card.Card> => {
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
            percentage: percent.toFixed(2),
            status,
          } as any, {
            default: () => `${row.leftAmountText}/${row.amountText}`,
          })
        }
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
  ]
}

const columns = createColumns()
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
  <NDataTable
    :columns="columns"
    :data="data"
    :bordered="false"
    :loading="tableLoading"
    scroll-x="max-content"
    remote
    :pagination="pagination"
  />
</template>
