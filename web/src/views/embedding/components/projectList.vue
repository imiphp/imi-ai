<script setup lang='ts'>
import { NButton, NCheckbox, NDataTable, NForm, NFormItem, NIcon, NInput, NModal, NSpace, NSpin, useDialog } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'

import { h, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ChatbubbleEllipsesOutline, CreateOutline, EyeOutline, TrashOutline } from '@vicons/ionicons5'
import { deleteProject, projectList, updateProject } from '@/api'
import { useEmbeddingStore } from '@/store/modules/embedding'
import { formatByte } from '@/utils/functions'

const router = useRouter()
const dialog = useDialog()
const embeddingState = useEmbeddingStore()

const createColumns = ({
  chat,
  view,
  update,
  del,
}: {
  chat: (row: Embedding.Project) => void
  view: (row: Embedding.Project) => void
  update: (row: Embedding.Project) => void
  del: (row: Embedding.Project) => void
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
      title: '文件总大小',
      key: 'totalFileSize',
      render(row) {
        return formatByte(row.totalFileSize)
      },
    },
    {
      title: '实际 Tokens',
      key: 'tokens',
    },
    {
      title: '支付 Tokens',
      key: 'payTokens',
    },
    {
      title: '状态',
      key: 'statusText',
    },
    {
      title: '权限',
      key: 'public',
      render(row) {
        return row.public ? '公开' : '私有'
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
              h(
                NButton,
                {
                  strong: true,
                  size: 'small',
                  type: 'primary',
                  renderIcon: () => h(NIcon, null, { default: () => h(EyeOutline) }),
                  onClick: () => view(row),
                },
                { default: () => '查看' },
              ),
              h(
                NButton,
                {
                  strong: true,
                  size: 'small',
                  type: 'default',
                  renderIcon: () => h(NIcon, null, { default: () => h(CreateOutline) }),
                  onClick: () => update(row),
                },
                { default: () => '编辑' },
              ),
              h(
                NButton,
                {
                  strong: true,
                  size: 'small',
                  type: 'error',
                  renderIcon: () => h(NIcon, null, { default: () => h(TrashOutline) }),
                  onClick: () => del(row),
                },
                { default: () => '删除' },
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
const editLoading = ref(false)
const showEditModal = ref(false)
const editData = ref<any>({})

const columns = createColumns({
  chat(row: Embedding.Project) {
    router.push({
      name: 'EmbeddingChat',
      params: {
        id: row.recordId,
      },
    })
  },
  view(row: Embedding.Project) {
    embeddingState.$state.currentProject = null
    router.push({
      name: 'ViewEmbeddingProject',
      params: {
        id: row.recordId,
      },
    })
  },
  update(row: Embedding.Project) {
    editData.value = { ...row }
    showEditModal.value = true
  },
  del(row: Embedding.Project) {
    dialog.warning({
      title: '删除询问',
      content: '确定要删除吗？',
      positiveText: '确定',
      negativeText: '取消',
      onPositiveClick: async () => {
        tableLoading.value = true
        try {
          await deleteProject(row.recordId)
          await loadProjectList()
        }
        finally {
          tableLoading.value = false
        }
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
    const response = await projectList(pagination.page, pagination.pageSize)
    data.value = response.list
    pagination.pageCount = response.pageCount
    pagination.itemCount = response.total
  }
  finally {
    tableLoading.value = false
  }
}

async function handleSaveButtonClient() {
  editLoading.value = true
  try {
    await updateProject(editData.value.recordId, { ...editData.value })
    editLoading.value = false
    showEditModal.value = false
    await loadProjectList()
  }
  finally {
    editLoading.value = false
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
  <NModal
    v-model:show="showEditModal"
    :mask-closable="false"
    :close-on-esc="!editLoading"
    :closable="!editLoading"
    preset="card"
    title="编辑项目"
    style="width: 95%; max-width: 640px"
  >
    <NSpin :show="editLoading">
      <NForm
        ref="formRef"
        :model="editData"
        label-placement="left"
        label-width="auto"
        require-mark-placement="right-hanging"
      >
        <NFormItem label="项目名称" path="name">
          <NInput v-model:value="editData.name" />
        </NFormItem>
        <NFormItem label="权限" path="name">
          <NCheckbox v-model:checked="editData.public" label="公开" />
        </NFormItem>
        <div style="display: flex; justify-content: flex-end">
          <NButton round type="primary" @click="handleSaveButtonClient">
            保存
          </NButton>
        </div>
      </NForm>
    </NSpin>
  </NModal>
</template>
