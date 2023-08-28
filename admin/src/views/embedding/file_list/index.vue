<template>
  <div class="overflow-hidden">
    <n-card :bordered="false" class="h-full rounded-8px shadow-sm" content-style="height:1px">
      <template #header>
        <n-button v-if="isMobile" class="p-0 w-11 h-11 align-middle" :bordered="false" @click="collapsed = !collapsed">
          <NIcon size="30" :component="collapsed ? ArrowForwardCircleOutline : ArrowBackCircleOutline" />
        </n-button>
        模型文件管理
      </template>
      <div class="flex-col h-full">
        <NLayout class="z-40 transition" has-sider>
          <NLayoutSider
            v-model:collapsed="collapsed"
            :collapsed-width="0"
            :width="260"
            collapse-mode="transform"
            position="absolute"
            bordered
            :style="getMobileClass"
            class="!absolute"
          >
            <NSpace vertical :size="12">
              <NTree
                v-model:selected-keys="selectedKeys"
                block-line
                expand-on-click
                :data="fileTreeData"
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
                    </p>
                  </NGi>
                  <NGi span="4 m:2 l:1">
                    <p>
                      <b>创建时间：</b>
                      {{ selectedFile.createTime > 0 ? new Date(selectedFile.createTime).toLocaleString() : '' }}
                    </p>
                  </NGi>
                  <NGi span="4 m:2 l:1">
                    <p>
                      <b>开始训练时间：</b>
                      {{
                        selectedFile.beginTrainingTime > 0
                          ? new Date(selectedFile.beginTrainingTime).toLocaleString()
                          : ''
                      }}
                    </p>
                  </NGi>
                  <NGi span="4 m:2 l:1">
                    <p>
                      <b>结束训练时间：</b>
                      {{
                        selectedFile.completeTrainingTime > 0
                          ? new Date(selectedFile.completeTrainingTime).toLocaleString()
                          : ''
                      }}
                    </p>
                  </NGi>
                  <NGi span="4 m:2 l:1">
                    <p>
                      <b>文件大小：</b>
                      <span v-text="formatByte(selectedFile.fileSize)" />
                    </p>
                  </NGi>
                  <NGi span="4 m:2 l:1">
                    <p>
                      <b>Token 数量：</b>
                      <span v-text="selectedFile.tokens" />
                    </p>
                  </NGi>
                  <NGi span="4 m:2 l:1">
                    <p>
                      <b>段落数量：</b>
                      <span v-text="sectionListData.length" />
                    </p>
                  </NGi>
                </NGrid>
              </NCard>
              <NGrid
                v-if="selectedFile"
                class="mt-2"
                x-gap="12"
                y-gap="16"
                :cols="3"
                item-responsive
                responsive="screen"
              >
                <NGi v-for="(item, index) of sectionListData" :key="index" span="3 m:2 l:1">
                  <NCard embedded>
                    <template #header>
                      <span
                        :style="EmbeddingStatus.FAILED === item.status ? 'color:red' : ''"
                        v-text="(index + 1).toString() + (item.title.length > 0 ? `. ${item.title}` : '')"
                      />
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
            <template v-if="isMobile">
              <div v-show="!collapsed" class="fixed inset-0 z-40 w-full h-full bg-black/40" @click="collapsed = true" />
            </template>
          </NLayoutContent>
        </NLayout>
      </div>
    </n-card>
    <!-- 查看段落模态框 -->
    <NModal
      v-if="currentSection"
      v-model:show="showViewSection"
      preset="card"
      title="查看"
      style="width: 1024px; max-width: 100vw; height: 1024px; max-height: 100vh"
    >
      <NTabs type="line" animated class="h-full" pane-wrapper-class="h-full" pane-class="h-full">
        <NTabPane name="content" tab="文本">
          <NInput :value="currentSection?.content" type="textarea" readonly show-count class="h-full" />
        </NTabPane>
        <NTabPane name="vector" tab="向量">
          <NInput :value="currentSection?.vector" type="textarea" readonly class="h-full" />
        </NTabPane>
        <NTabPane name="info" tab="信息">
          <NDescriptions label-placement="top" :column="2" label-style="font-weight: bold">
            <NDescriptionsItem label="创建时间">
              {{ currentSection?.createTime > 0 ? new Date(currentSection?.createTime).toLocaleString() : '' }}
            </NDescriptionsItem>
            <NDescriptionsItem label="状态">
              <NText>{{ currentSection?.statusText }}</NText>
            </NDescriptionsItem>
            <NDescriptionsItem label="开始训练时间">
              {{
                currentSection?.beginTrainingTime > 0
                  ? new Date(currentSection?.beginTrainingTime).toLocaleString()
                  : ''
              }}
            </NDescriptionsItem>
            <NDescriptionsItem label="结束训练时间">
              {{
                currentSection?.completeTrainingTime > 0
                  ? new Date(currentSection?.completeTrainingTime).toLocaleString()
                  : ''
              }}
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
      <NInput :value="selectedFile?.content" type="textarea" readonly show-count class="h-full" />
    </NModal>
  </div>
</template>

<script setup lang="tsx">
import { computed, ref, watch, nextTick } from 'vue';
import type { Ref, CSSProperties } from 'vue';
import { useRoute } from 'vue-router';
import type { TreeOption } from 'naive-ui';
import { ArrowBackOutline, BookOutline, ArrowForwardCircleOutline, ArrowBackCircleOutline } from '@vicons/ionicons5';
import {
  fetchEmbeddingAssocFileList,
  fetchEmbeddingFileList,
  fetchEmbeddingProject,
  fetchEmbeddingSectionList,
  EmbeddingStatus,
  fetchEmbeddingFile,
  fetchEmbeddingSection
} from '@/service';
import { useLoading } from '@/hooks';
import { useBasicLayout } from '~/src/composables';
import { defaultPaginationProps } from '~/src/utils';
import { formatByte } from '~/src/utils/auth';
import { decodeSecureField } from '~/src/utils/crypto';

const { isMobile } = useBasicLayout();
const route = useRoute();
const projectId = parseInt(route.query.projectId?.toString() ?? '0');
const { startLoading, endLoading } = useLoading(false);

const tableData = ref<Embedding.File[]>([]);

const pagination = defaultPaginationProps(getTableData);

const fileTreeData: Ref<any> = ref([]);
const selectedKeys = ref<Array<string | number>>([]);
const selectedFileId = computed(() => selectedKeys.value[0]);
const selectedFile = ref<Embedding.File | null>(null);
const sectionListData = ref<Array<Embedding.Section>>([]);
const showViewSection = ref(false);
const currentSection = ref<Embedding.Section | null>(null);
const showViewContent = ref(false);
const showLoading = ref(false);
const loadingFile = ref(false);
const collapsed = ref(false);

function setTableData(response: Embedding.FileListResponse) {
  tableData.value = response.list;
  pagination.pageCount = 1;
  pagination.itemCount = response.list.length;
}

async function getTableData() {
  startLoading();
  try {
    const { data } = await fetchEmbeddingFileList(projectId);
    if (data) {
      setTableData(data);
    }
  } finally {
    endLoading();
  }
}

const getMobileClass = computed<CSSProperties>(() => {
  if (isMobile.value) {
    return {
      position: 'fixed',
      zIndex: 50
    };
  }
  return {};
});

const getContainerClass = computed(() => {
  return ['h-full', { 'pl-[260px]': !isMobile.value }];
});

function handleSelectKeys(
  keys: Array<string & number>,
  _option: Array<TreeOption | null>,
  meta: {
    node: TreeOption | null;
    action: 'select' | 'unselect';
  }
): void {
  if (meta.action === 'unselect') return;

  if (!meta.node?.children) {
    selectedKeys.value = keys;
    selectedFile.value = { ...meta.node } as unknown as Embedding.File;
    if (selectedFile.value.baseName) selectedFile.value.baseName = decodeSecureField(selectedFile.value.baseName);
    selectedFile.value.fileName = decodeSecureField(selectedFile.value.fileName);
  }
}

function onRenderTreeLabel({ option }: any) {
  const result = decodeSecureField(option.baseName);
  return result;
}

async function loadSectionList() {
  try {
    showLoading.value = true;
    if (!selectedFile.value) {
      return;
    }
    const response = await fetchEmbeddingSectionList(projectId, selectedFile.value.id);
    if (!response.data) {
      return;
    }
    sectionListData.value = response.data.list;
  } finally {
    showLoading.value = false;
  }
}

watch(selectedFileId, async () => {
  await loadSectionList();
});

watch(isMobile, value => {
  if (!value) {
    collapsed.value = false;
  }
});

async function viewFileContent() {
  if (!selectedFile.value) return;
  if (!selectedFile.value.content) {
    loadingFile.value = true;
    try {
      const { data } = await fetchEmbeddingFile(selectedFile.value.id);
      if (!data) {
        return;
      }
      nextTick(() => (selectedFile.value = data.data));
    } finally {
      loadingFile.value = false;
    }
  }
  showViewContent.value = true;
}

async function viewSection(section: Embedding.Section) {
  if (!section.vector) {
    loadingFile.value = true;
    try {
      const { data } = await fetchEmbeddingSection(section.id);
      if (!data) {
        return;
      }
      nextTick(() => (section.vector = data.data.vector));
    } finally {
      loadingFile.value = false;
    }
  }
  currentSection.value = section;
  showViewSection.value = true;
}

async function loadInfo() {
  const promises: any = [fetchEmbeddingProject(projectId), fetchEmbeddingAssocFileList(projectId)];
  if (selectedFile.value) promises.push(fetchEmbeddingSectionList(projectId, selectedFile.value.id));

  const promiseResult = await Promise.all(promises);
  const [_projectResponse, assocFileListResponse] = promiseResult;

  fileTreeData.value = assocFileListResponse.data.list;

  if (selectedFileId.value) {
    sectionListData.value = promiseResult[2].list;
    for (const item of assocFileListResponse.data.list) {
      if (selectedFileId.value === item.recordId) {
        selectedFile.value = { ...item } as unknown as Embedding.File;
        if (selectedFile.value.baseName) selectedFile.value.baseName = decodeSecureField(selectedFile.value.baseName);
        selectedFile.value.fileName = decodeSecureField(selectedFile.value.fileName);
        break;
      }
    }
  }
}

async function init() {
  getTableData();
  loadInfo();
}

// 初始化
init();
</script>

<style scoped></style>
