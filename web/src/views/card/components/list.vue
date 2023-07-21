<script setup lang='ts'>
import { NDataTable } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'
import { onMounted, reactive, ref } from 'vue'
import { cardList } from '@/api'
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
      title: '初始金额',
      key: 'amountText',
    },
    {
      title: '实时余额',
      key: 'leftAmountText',
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
        return row.expireTime > 0 ? new Date(row.expireTime * 1000).toLocaleString() : '永久有效'
      },
    },
  ]
}

const columns = createColumns()
const tableLoading = ref(false)
const expired = ref<boolean | null>(null)
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
    const response = await cardList(expired.value, pagination.page, pagination.pageSize)
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
