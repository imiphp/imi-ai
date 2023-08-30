<script setup lang='tsx'>
import { NButton, NCard, NCheckbox, NDataTable, NForm, NFormItem, NIcon, NInput, NInputNumber, NModal, NSpace, NSpin, NSwitch, NTabPane, NTabs, useDialog } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'

import { h, onMounted, onUnmounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { ChatbubbleEllipsesOutline, CreateOutline, EyeOutline, Refresh, TrashOutline } from '@vicons/ionicons5'
import { config, deleteProject, projectList, retryProject, updateProject } from '@/api'
import { EmbeddingStatus, PublicProjectStatus, useEmbeddingStore } from '@/store/modules/embedding'
import { formatByte } from '@/utils/functions'
import { t } from '@/locales'
import Advanced from '@/components/common/Setting/Advanced.vue'
import { defaultChatSetting } from '@/store'

const router = useRouter()
const dialog = useDialog()
const embeddingState = useEmbeddingStore()
const models = ref({})

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
      title: 'Tokens（实际/支付）',
      key: 'tokens',
      render(row) {
        return `${row.tokens}/${row.payTokens}`
      },
    },
    {
      title: '状态',
      key: 'statusText',
      render(row) {
        if (row.status === EmbeddingStatus.FAILED) {
          return [h('span', { style: 'color:#d03050' }, row.statusText), h(
            NButton,
            {
              strong: true,
              size: 'tiny',
              style: 'margin-left: 4px; vertical-align: bottom;',
              renderIcon: () => h(NIcon, null, { default: () => h(Refresh) }),
              onClick: () => retry(row),
            },
          )]
        }
        else if (row.status === EmbeddingStatus.TRAINING) {
          return <>
            <span>{row.statusText}</span>
            <NSpin size={18} class="align-sub ml-1" />
          </>
        }
        else { return row.statusText }
      },
    },
    {
      title: '权限',
      key: 'public',
      render(row) {
        const nswitch = h(NSwitch, {
          value: row.public,
          onUpdateValue: (value: boolean) => {
            dialog.warning({
              title: '询问',
              content: `是否将该项目设为${value ? '公开' : '私有'}？`,
              positiveText: t('common.yes'),
              negativeText: t('common.no'),
              onPositiveClick: async () => {
                (async () => {
                  try {
                    if (nswitch.component)
                      nswitch.component.props.loading = true
                    await updateProject(row.recordId, { public: value })
                    row.public = value
                  }
                  finally {
                    if (nswitch.component)
                      nswitch.component.props.loading = false
                  }
                })()
              },
            })
          },
        }, {
          checked: () => '公开',
          unchecked: () => '私有',
        })
        return nswitch
      },
    },
    {
      title: '列表公开',
      key: 'publicList',
      render(row) {
        const nswitch = h(NSwitch, {
          value: row.publicList || PublicProjectStatus.WAIT_FOR_REVIEW === row.publicProject?.status,
          onUpdateValue: (value: boolean) => {
            dialog.warning({
              title: '询问',
              content: value ? '是否将该项目设为列表公开？' : '是否将该项目从公开列表删除？',
              positiveText: t('common.yes'),
              negativeText: t('common.no'),
              onPositiveClick: async () => {
                (async () => {
                  try {
                    if (nswitch.component)
                      nswitch.component.props.loading = true
                    await updateProject(row.recordId, { publicList: value })
                    row.publicList = value
                  }
                  finally {
                    if (nswitch.component)
                      nswitch.component.props.loading = false
                  }
                  await loadProjectList()
                })()
              },
            })
          },
        }, {
          checked: () => {
            return PublicProjectStatus.WAIT_FOR_REVIEW === row.publicProject?.status ? '等待审核' : '公开'
          },
          unchecked: () => '私有',
        })
        return nswitch
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

let loadProjectListTimer: NodeJS.Timeout | null = null
const tableLoading = ref(false)
const data = ref<Embedding.Project[]>([])
const editLoading = ref(false)
const showEditModal = ref(false)
const editData = ref<any>({})
const editProject = ref<Embedding.Project | null>(null)
const indeterminate = ref(false)
const publicConfig = ref<any>(null)

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
    editData.value.chatConfig = { ...defaultChatSetting(), ...row.chatConfig }
    editProject.value = editData.value
    indeterminate.value = PublicProjectStatus.WAIT_FOR_REVIEW === editData.value.publicProject?.status
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

async function loadProjectList(loading = true) {
  if (loadProjectListTimer) {
    clearTimeout(loadProjectListTimer)
    loadProjectListTimer = null
  }
  if (loading)
    tableLoading.value = true
  try {
    const response = await projectList(pagination.page, pagination.pageSize)
    data.value = response.list
    pagination.pageCount = response.pageCount
    pagination.itemCount = response.total
    for (const item of data.value) {
      if (!loadProjectListTimer && (item.status === EmbeddingStatus.EXTRACTING || item.status === EmbeddingStatus.TRAINING)) {
        loadProjectListTimeout()
        return
      }
    }
  }
  finally {
    if (loading)
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

function loadProjectListTimeout() {
  if (loadProjectListTimer)
    clearTimeout(loadProjectListTimer)

  loadProjectListTimer = setTimeout(async () => {
    loadProjectListTimer = null
    await loadProjectList(false)
  }, 1500)
}

async function retry(row: Embedding.Project) {
  dialog.warning({
    title: '重试',
    content: '是否重试训练该项目？',
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      await retryProject(row.recordId)
      await loadProjectList(false)
    },
  })
}

function handleClickPublicProject() {
  if (indeterminate.value)
    editData.value.publicList = indeterminate.value = false
}

async function loadConfig() {
  const response = await config()
  publicConfig.value = response.data
  models.value = response.data['config:embedding'].config.chatModelConfig ?? []
}

onMounted(async () => {
  await Promise.all([
    loadProjectList(),
    loadConfig(),
  ])
})

onUnmounted(() => {
  if (loadProjectListTimer) {
    clearTimeout(loadProjectListTimer)
    loadProjectListTimer = null
  }
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
        <NCard :bordered="false" content-style="padding:0">
          <NTabs
            default-value="info"
            animated
          >
            <NTabPane name="info" tab="基本信息">
              <NFormItem label="项目名称">
                <NInput v-model:value="editData.name" />
              </NFormItem>
              <NFormItem label="权限">
                <NCheckbox v-model:checked="editData.public" label="公开" />
              </NFormItem>
              <NFormItem label="在列表公开">
                <NCheckbox v-model:checked="editData.publicList" :disabled="!editData.public" :indeterminate="indeterminate" label="公开" @click="handleClickPublicProject" />
                <span v-if="PublicProjectStatus.WAIT_FOR_REVIEW === editProject?.publicProject?.status">（等待审核）</span>
              </NFormItem>
              <NFormItem label="分隔符">
                <NInput v-model:value="editData.sectionSeparator" placeholder="用于分割段落，支持转义符" />
              </NFormItem>
              <NFormItem label="段落最大长度">
                <NInputNumber v-model:value="editData.sectionSplitLength" :max="publicConfig['config:embedding'].config.maxSectionTokens" />
              </NFormItem>
              <NFormItem label="按标题分割段落">
                <NCheckbox v-model:checked="editData.sectionSplitByTitle" />
              </NFormItem>
            </NTabPane>
            <NTabPane name="chatConfig" tab="对话配置">
              <Advanced v-model:setting="editData.chatConfig" :models="models" :show-confirm="false" />
            </NTabPane>
          </NTabs>
        </NCard>
        <div style="display: flex; justify-content: flex-end">
          <NButton round type="primary" @click="handleSaveButtonClient">
            保存
          </NButton>
        </div>
      </NForm>
    </NSpin>
  </NModal>
</template>
