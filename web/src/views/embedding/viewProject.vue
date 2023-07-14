<script setup lang='ts'>
import type { TreeOption } from 'naive-ui'
import { NBreadcrumb, NBreadcrumbItem, NButton, NCard, NDescriptions, NDescriptionsItem, NDivider, NEllipsis, NEmpty, NGi, NGrid, NIcon, NInput, NLayout, NLayoutContent, NLayoutSider, NModal, NSpin, NTabPane, NTabs, NText, NTree } from 'naive-ui'
import type { CSSProperties, Ref } from 'vue'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

import { useRoute } from 'vue-router'
import { ArrowBackOutline, BookOutline } from '@vicons/ionicons5'
import HeaderComponent from '../layout/components/Header/index.vue'
import { assocFileList, getProject, sectionList } from '@/api'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { useAppStore, useRuntimeStore } from '@/store'
import { EmbeddingStatus, useEmbeddingStore } from '@/store/modules/embedding'
import { formatByte } from '@/utils/functions'
import { Time } from '@/components/common'
import { decodeSecureField } from '@/utils/request'

const appStore = useAppStore()
const route = useRoute()
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

const { isMobile } = useBasicLayout()

const collapsed = computed(() => appStore.siderCollapsed)

let timer: NodeJS.Timeout | null = null

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

function onRenderTreeLabel({ option }: any) {
  const result = decodeSecureField(option.baseName)
  return result
}

onMounted(async () => {
  try {
    showLoading.value = true
    const projectPromise = getProject(id)
    const assocFileListPromise = assocFileList(id)
    const [projectResponse, assocFileListResponse] = await Promise.all([projectPromise, assocFileListPromise])
    embeddingState.$state.currentProject = projectResponse.data
    runtimeStore.$state.headerTitle = projectResponse.data.name
    data.value = assocFileListResponse.list
    if (projectResponse.data.status === EmbeddingStatus.EXTRACTING || projectResponse.data.status === EmbeddingStatus.TRAINING) {
      timer = setInterval(async () => {
        const projectResponse = (await getProject(id))
        embeddingState.$state.currentProject = projectResponse.data
        if (timer && (projectResponse.data.status === EmbeddingStatus.COMPLETED || projectResponse.data.status === EmbeddingStatus.FAILED)) {
          clearInterval(timer)
          timer = null
        }
      }, 1500)
    }
  }
  finally {
    showLoading.value = false
  }
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
          首页
        </NBreadcrumbItem>
        <NBreadcrumbItem>
          <RouterLink to="/embedding">
            模型训练
          </RouterLink>
        </NBreadcrumbItem>
        <NBreadcrumbItem>
          <span v-text="embeddingState.$state.currentProject?.name || '加载中'" />
        </nbreadcrumbitem>
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
      </NLayoutSider>
      <NLayoutContent :class="getContainerClass">
        <NCard :bordered="false">
          <NCard v-if="selectedFile" :title="selectedFile.fileName">
            <template #header-extra>
              <NButton type="info" @click="showViewContent = true">
                <template #icon>
                  <NIcon><BookOutline /></NIcon>
                </template>
                查看全文
              </NButton>
            </template>
            <NGrid x-gap="12" y-gap="16" :cols="4" item-responsive responsive="screen">
              <NGi span="4 m:2 l:1">
                <p><b>状态：</b><span v-text="selectedFile.statusText" /></p>
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
              <NCard :title="(index + 1).toString()" embedded>
                <a href="javascript:;" @click="viewSection(item)">
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
</template>
