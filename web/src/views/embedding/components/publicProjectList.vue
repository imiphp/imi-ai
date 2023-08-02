<script setup lang='ts'>
import { NButton, NDataTable, NIcon, NSpace } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'

import { h, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ChatbubbleEllipsesOutline } from '@vicons/ionicons5'
import { publicProjectList } from '@/api'
import { EmbeddingStatus } from '@/store/modules/embedding'
import { formatByte } from '@/utils/functions'

const router = useRouter()

const createColumns = ({
  chat,
}: {
  chat: (row: Embedding.Project) => void
}): DataTableColumns<Embedding.Project> => {
  return [
    {
      title: 'ID',
      key: 'recordId',
    },
    {
      title: '名称',
      key: 'name',
    },
    {
      title: '作者',
      key: 'memberInfo.nickname',
    },
    {
      title: '文件总大小',
      key: 'totalFileSize',
      render(row) {
        return formatByte(row.totalFileSize)
      },
    },
    {
      title: '状态',
      key: 'statusText',
      render(row) {
        if (row.status === EmbeddingStatus.FAILED)
          return [h('span', { style: 'color:#d03050' }, row.statusText)]

        else return row.statusText
      },
    },
    {
      title: '创建时间',
      key: 'createTime',
      render(row) {
        return new Date(row.createTime).toLocaleString()
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
                  renderIcon: () => h(NIcon, null, { default: () => h(ChatbubbleEllipsesOutline) }),
                  onClick: () => chat(row),
                },
                { default: () => '对话' },
              ),
            ],
          },
        )
      },
    },
  ]
}

const tableLoading = ref(false)
const data = ref<Embedding.Project[]>([])

const columns = createColumns({
  chat(row: Embedding.Project) {
    router.push({
      name: 'EmbeddingChat',
      params: {
        id: row.recordId,
      },
    })
  },
})

const pagination = reactive({
  page: 1,
  pageSize: 15,
  pageCount: 1,
  itemCount: 0,
  onChange: (page: number) => {
    pagination.page = page
    loadProjectList()
  },
  onUpdatePageSize: (pageSize: number) => {
    pagination.pageSize = pageSize
    pagination.page = 1
  },
})

async function loadProjectList() {
  tableLoading.value = true
  try {
    const response = await publicProjectList(pagination.page, pagination.pageSize)
    data.value = response.list
    pagination.pageCount = response.pageCount
    pagination.itemCount = response.total
  }
  finally {
    tableLoading.value = false
  }
}

onMounted(async () => {
  await loadProjectList()
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
