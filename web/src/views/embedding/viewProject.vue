<script setup lang='ts'>
import type { TreeOption, UploadFileInfo, UploadInst } from 'naive-ui'
import { NBreadcrumb, NBreadcrumbItem, NButton, NCard, NDescriptions, NDescriptionsItem, NDivider, NEllipsis, NEmpty, NForm, NFormItem, NGi, NGrid, NIcon, NInput, NLayout, NLayoutContent, NLayoutSider, NModal, NRadio, NRadioGroup, NSpace, NSpin, NTabPane, NTabs, NText, NTree, NUpload, useDialog } from 'naive-ui'

import type { CSSProperties, Ref } from 'vue'
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'

import { useRoute, useRouter } from 'vue-router'
import { ArrowBackOutline, BookOutline, CloudUploadOutline, Refresh } from '@vicons/ionicons5'
import HeaderComponent from '../layout/components/Header/index.vue'
import { assocFileList, getFile, getProject, retryFile, retrySection, sectionList } from '@/api'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { useAppStore, useAuthStore, useRuntimeStore } from '@/store'
import { EmbeddingStatus, useEmbeddingStore } from '@/store/modules/embedding'
import { formatByte } from '@/utils/functions'
import { Time } from '@/components/common'
import { decodeSecureField } from '@/utils/request'
import service from '@/utils/request/axios'
import { t } from '@/locales'

const appStore = useAppStore()
const route = useRoute()
const dialog = useDialog()
const router = useRouter()
const embeddingState = useEmbeddingStore()
const runtimeStore = useRuntimeStore()
const id = route.params.id.toString()
const data: Ref<any> = ref([])
const selectedKeys = ref<Array<string | number>>([])
const selectedFileId = computed(() => selectedKeys.value[0])
const selectedFile = ref<Embedding.File | null>(null)
const sectionListData = ref<Array<Embedding.Section>>([])
const showViewSection = ref(false)
const currentSection = ref<Embedding.Section | null>(null)
const showViewContent = ref(false)
const showLoading = ref(false)
const fileList = ref<UploadFileInfo[]>([])
const uploadHeaders = ref({
  Authorization: `Bearer ${useAuthStore().token}`,
})
const uploadActionUrl = service.getUri({
  url: '/embedding/openai/upload',
})
const uploadData = ref<any>({
  id,
  override: false,
  directory: '',
})
const upload = ref<UploadInst | null>(null)
const loadingFile = ref(false)
const showUploadModal = ref(false)
watch(showUploadModal, (value) => {
  if (!value)
    fileList.value = []
})
const uploadFileName = ref('')

const { isMobile } = useBasicLayout()

const collapsed = computed(() => appStore.siderCollapsed)

let timer: NodeJS.Timer | null = null

const getMobileClass = computed<CSSProperties>(() => {
  if (isMobile.value) {
    return {
      position: 'fixed',
      zIndex: 50,
    }
  }
  return {}
})

const getContainerClass = computed(() => {
  return [
    'h-full',
    { 'pl-[260px]': !isMobile.value && !collapsed.value },
  ]
})

function handleUpdateCollapsed() {
  appStore.setSiderCollapsed(!collapsed.value)
}

function handleSelectKeys(keys: Array<string & number>, option: Array<TreeOption | null>, meta: {
  node: TreeOption | null
  action: 'select' | 'unselect'
}): void {
  if (meta.action === 'unselect')
    return

  if (!meta.node?.children) {
    selectedKeys.value = keys
    selectedFile.value = { ...meta.node } as unknown as Embedding.File
    selectedFile.value.baseName = decodeSecureField(selectedFile.value.baseName)
    selectedFile.value.fileName = decodeSecureField(selectedFile.value.fileName)
  }
}

async function loadSectionList() {
  try {
    showLoading.value = true
    const response = await sectionList(id, selectedFileId.value.toString())
    sectionListData.value = response.list
  }
  finally {
    showLoading.value = false
  }
}

watch(selectedFileId, async () => {
  await loadSectionList()
})

async function viewSection(section: Embedding.Section) {
  currentSection.value = section
  showViewSection.value = true
}

function getUploadData() {
  const data = { ...uploadData.value }
  data.override = data.override ? '1' : '0'
  return data
}

function onRenderTreeLabel({ option }: any) {
  const result = decodeSecureField(option.baseName)
  return result
}

function handleChange(options: { file: UploadFileInfo; fileList: Array<UploadFileInfo>; event?: Event }) {
  uploadFileName.value = options.file.name
  showUploadModal.value = true
}

function handleUpload() {
  showLoading.value = true
  upload.value?.submit()
}

const onFinish = (options: { event?: ProgressEvent }) => {
  try {
    const response = (options.event?.target as XMLHttpRequest)?.response
    if (response) {
      const data = JSON.parse(response)
      if (data.code === 0) {
        router.push({
          name: 'ViewEmbeddingProject',
          params: {
            id: data.data.recordId,
          },
        })
      }
      else {
        dialog.error({
          title: '错误',
          content: data.message || '上传失败，未知原因',
          positiveText: '确定',
        })
      }
    }
    else {
      dialog.error({
        title: '错误',
        content: '上传失败，未知原因',
        positiveText: '确定',
      })
    }
  }
  finally {
    nextTick(() => {
      fileList.value = []
      showUploadModal.value = false
      showLoading.value = false
      uploadData.value = {
        id,
        override: false,
        directory: '',
      }
      loadInfo()
    })
  }
}

async function loadInfo(allowNewTimer = true) {
  try {
    if (allowNewTimer)
      showLoading.value = true
    const promises: any = [getProject(id), assocFileList(id)]
    if (selectedFileId.value)
      promises.push(sectionList(id, selectedFileId.value.toString()))

    const promiseResult = await Promise.all(promises)
    const [projectResponse, assocFileListResponse] = promiseResult

    if (projectResponse.data.status === EmbeddingStatus.EXTRACTING || projectResponse.data.status === EmbeddingStatus.TRAINING) {
      if (allowNewTimer) {
        if (timer)
          clearInterval(timer)
        timer = setInterval(async () => {
          loadInfo(false)
        }, 1500)
      }
    }
    else {
      if (timer)
        clearInterval(timer)
    }

    embeddingState.$state.currentProject = projectResponse.data
    runtimeStore.$state.headerTitle = projectResponse.data.name
    data.value = assocFileListResponse.list

    if (selectedFileId.value) {
      sectionListData.value = promiseResult[2].list
      for (const item of assocFileListResponse.list) {
        if (selectedFileId.value === item.recordId) {
          selectedFile.value = { ...item } as unknown as Embedding.File
          selectedFile.value.baseName = decodeSecureField(selectedFile.value.baseName)
          selectedFile.value.fileName = decodeSecureField(selectedFile.value.fileName)
          break
        }
      }
    }
  }
  finally {
    if (allowNewTimer)
      showLoading.value = false
  }
}

async function handleRetryFile() {
  if (!selectedFile.value) {
    dialog.error({
      title: '错误',
      content: '未选择文件',
      positiveText: '确定',
    })
    return
  }
  dialog.warning({
    title: '重试',
    content: '是否重试训练该文件？',
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      if (!selectedFile.value)
        return

      await retryFile(selectedFile.value.recordId)
      await loadInfo()
    },
  })
}

async function handleRetrySection(id: string) {
  dialog.warning({
    title: '重试',
    content: '是否重试训练该段落？',
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      await retrySection(id)
      await loadInfo()
    },
  })
}

async function viewFileContent() {
  if (!selectedFile.value)
    return
  if (!selectedFile.value.content) {
    loadingFile.value = true
    const response = await getFile(selectedFile.value.recordId)
    selectedFile.value = response.data
    loadingFile.value = false
  }
  showViewContent.value = true
}

onMounted(async () => {
  await loadInfo()
})

onUnmounted(() => {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
})
</script>

<template>
  <NSpin v-show="showLoading" class="spin-loading" />
  <NCard class="!h-[calc(100%-49px)]" :bordered="false" content-style="padding:0">
    <HeaderComponent
      v-if="isMobile"
    />
    <div v-if="!isMobile">
      <NBreadcrumb class="!leading-[24px]">
        <NBreadcrumbItem>
          <RouterLink to="/">
            首页
          </RouterLink>
        </NBreadcrumbItem>
        <NBreadcrumbItem>
          <RouterLink to="/embedding">
            模型训练
          </RouterLink>
        </NBreadcrumbItem>
        <NBreadcrumbItem>
          <span v-text="embeddingState.$state.currentProject?.name || '加载中'" />
        </NBreadcrumbItem>
      </NBreadcrumb>
      <NDivider class="!mt-[2px] !mb-[2px]" />
    </div>
    <NLayout class="z-40 transition h-full" has-sider>
      <NLayoutSider
        :collapsed="collapsed"
        :collapsed-width="0"
        :width="260"
        :show-trigger="isMobile ? false : 'arrow-circle'"
        collapse-mode="transform"
        position="absolute"
        bordered
        :style="getMobileClass"
        @update-collapsed="handleUpdateCollapsed"
      >
        <NSpace vertical :size="12">
          <NUpload
            ref="upload"
            v-model:file-list="fileList"
            trigger-style="display:block"
            :show-file-list="false"
            :default-upload="false"
            :data="getUploadData()"
            :action="uploadActionUrl"
            :max="1"
            :on-finish="onFinish"
            :headers="uploadHeaders"
            @change="handleChange"
          >
            <NButton block type="primary" secondary>
              <template #icon>
                <NIcon>
                  <CloudUploadOutline />
                </NIcon>
              </template>
              上传文件
            </NButton>
          </NUpload>
          <NTree
            v-model:selected-keys="selectedKeys"
            block-line
            expand-on-click
            :data="data"
            key-field="recordId"
            label-field="baseName"
            :on-update:selected-keys="handleSelectKeys"
            :watch-props="['defaultExpandedKeys', 'defaultCheckedKeys', 'defaultSelectedKeys']"
            :render-label="onRenderTreeLabel"
          />
        </NSpace>
      </NLayoutSider>
      <NLayoutContent :class="getContainerClass">
        <NCard :bordered="false">
          <NCard v-if="selectedFile" :title="selectedFile.fileName">
            <template #header-extra>
              <NButton type="info" :loading="loadingFile" @click="viewFileContent()">
                <template #icon>
                  <NIcon><BookOutline /></NIcon>
                </template>
                查看全文
              </NButton>
            </template>
            <NGrid x-gap="12" y-gap="16" :cols="4" item-responsive responsive="screen">
              <NGi span="4 m:2 l:1">
                <p>
                  <b>状态：</b>
                  <span v-text="selectedFile.statusText" />
                  <NButton v-if="EmbeddingStatus.FAILED === selectedFile.status" size="tiny" class="!ml-[1em] align-middle" @click="handleRetryFile()">
                    <template #icon>
                      <NIcon><Refresh /></NIcon>
                    </template>
                    重试
                  </NButton>
                </p>
              </NGi>
              <NGi span="4 m:2 l:1">
                <p><b>创建时间：</b><Time :time="selectedFile.createTime" /></p>
              </NGi>
              <NGi span="4 m:2 l:1">
                <p><b>开始训练时间：</b><Time :time="selectedFile.beginTrainingTime" /></p>
              </NGi>
              <NGi span="4 m:2 l:1">
                <p><b>结束训练时间：</b><Time :time="selectedFile.completeTrainingTime" /></p>
              </NGi>
              <NGi span="4 m:2 l:1">
                <p><b>文件大小：</b><span v-text="formatByte(selectedFile.fileSize)" /></p>
              </NGi>
              <NGi span="4 m:2 l:1">
                <p><b>Token 数量：</b><span v-text="selectedFile.tokens" /></p>
              </NGi>
              <NGi span="4 m:2 l:1">
                <p><b>段落数量：</b><span v-text="sectionListData.length" /></p>
              </NGi>
            </NGrid>
          </NCard>
          <NGrid v-if="selectedFile" class="mt-2" x-gap="12" y-gap="16" :cols="4" item-responsive responsive="screen">
            <NGi v-for="(item, index) of sectionListData" :key="index" span="4 m:2 l:1">
              <NCard embedded>
                <template #header>
                  <span :style="EmbeddingStatus.FAILED === item.status ? 'color:red' : ''" v-text="(index + 1).toString()" />
                </template>
                <template #header-extra>
                  <NButton v-if="EmbeddingStatus.FAILED === item.status" size="tiny" @click="handleRetrySection(item.recordId)">
                    <template #icon>
                      <NIcon><Refresh /></NIcon>
                    </template>
                    重试
                  </NButton>
                </template>
                <a class="block" href="javascript:;" @click="viewSection(item)">
                  <NEllipsis :line-clamp="8" :tooltip="false" class="hover:text-gray-500">
                    <p v-text="item.content" />
                  </NEllipsis>
                </a>
              </NCard>
            </NGi>
          </NGrid>
          <NEmpty v-if="!selectedFile" description="请在左侧文件列表选择文件">
            <template #icon>
              <NIcon>
                <ArrowBackOutline />
              </NIcon>
            </template>
          </NEmpty>
        </NCard>
      </NLayoutContent>
    </NLayout>
  </NCard>
  <template v-if="isMobile">
    <div v-show="!collapsed" class="fixed inset-0 z-40 w-full h-full bg-black/40" @click="handleUpdateCollapsed" />
  </template>
  <!-- 查看段落模态框 -->
  <NModal
    v-model:show="showViewSection"
    preset="card"
    title="查看"
    style="width: 1024px; max-width: 100vw; height: 1024px; max-height: 100vh"
  >
    <NTabs type="line" animated class="h-full" pane-wrapper-class="h-full" pane-class="h-full">
      <NTabPane name="content" tab="文本">
        <NInput
          :value="currentSection?.content"
          type="textarea"
          readonly
          show-count
          class="h-full"
        />
      </NTabPane>
      <NTabPane name="vector" tab="向量">
        <NInput
          :value="currentSection?.vector"
          type="textarea"
          readonly
          class="h-full"
        />
      </NTabPane>
      <NTabPane name="info" tab="信息">
        <NDescriptions label-placement="top" :column="2" label-style="font-weight: bold">
          <NDescriptionsItem label="创建时间">
            <Time :time="currentSection?.createTime" />
          </NDescriptionsItem>
          <NDescriptionsItem label="状态">
            <NText>{{ currentSection?.statusText }}</NText>
          </NDescriptionsItem>
          <NDescriptionsItem label="开始训练时间">
            <Time :time="currentSection?.beginTrainingTime" />
          </NDescriptionsItem>
          <NDescriptionsItem label="结束训练时间">
            <Time :time="currentSection?.completeTrainingTime" />
          </NDescriptionsItem>
          <NDescriptionsItem label="Token 数量">
            <NText>{{ currentSection?.tokens }}</NText>
          </NDescriptionsItem>
        </NDescriptions>
      </NTabPane>
    </NTabs>
  </NModal>
  <!-- 查看全文模态框 -->
  <NModal
    v-model:show="showViewContent"
    preset="card"
    title="查看全文"
    style="width: 1024px; max-width: 100vw; height: 1024px; max-height: 100vh"
  >
    <NInput
      :value="selectedFile?.content"
      type="textarea"
      readonly
      show-count
      class="h-full"
    />
  </NModal>
  <!-- 上传文件模态框 -->
  <NModal
    v-model:show="showUploadModal"
    preset="card"
    title="上传文件"
    style="width: 512px; max-width: 100vw; max-height: 100vh"
    :mask-closable="false"
  >
    <NForm label-placement="left">
      <NFormItem label="文件名：">
        <span v-text="uploadFileName" />
      </NFormItem>
      <NFormItem label="目标目录：">
        <NInput v-model:value="uploadData.directory" placeholder="留空则为根目录" />
      </NFormItem>
      <NFormItem label="同名文件：">
        <NRadioGroup v-model:value="uploadData.override" name="override">
          <NRadio :value="false" label="跳过" />
          <NRadio :value="true" label="覆盖" />
        </NRadioGroup>
      </NFormItem>
      <NFormItem>
        <NButton type="primary" @click="handleUpload()">
          上传
        </NButton>
      </NFormItem>
    </NForm>
  </NModal>
</template>
