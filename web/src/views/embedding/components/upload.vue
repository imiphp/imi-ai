<script setup lang='tsx'>
import type { UploadFileInfo, UploadInst } from 'naive-ui'
import { NButton, NCheckbox, NForm, NFormItem, NIcon, NInput, NInputNumber, NModal, NP, NText, NUpload, NUploadDragger, useDialog } from 'naive-ui'

import { ArchiveOutline as ArchiveIcon } from '@vicons/ionicons5'
import { nextTick, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import service from '@/utils/request/axios'
import { useAuthStore } from '@/store'
import { config, embeddingFileTypes } from '@/api'

const dialog = useDialog()
const router = useRouter()

const uploadActionUrl = service.getUri({
  url: '/embedding/openai/upload',
})

const fileList = ref<UploadFileInfo[]>([])
const uploadHeaders = ref({
  Authorization: `Bearer ${useAuthStore().token}`,
})

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
    })
  }
}

const compressFileTypes = ref([])
const contentFileTypes = ref([])
const publicConfig = ref<any>(null)
const showUploadSetting = ref(false)
const showUploadSettingByUpload = ref(false)

async function loadFileTypes() {
  const response = await embeddingFileTypes()
  compressFileTypes.value = response.compressFileTypes
  contentFileTypes.value = response.contentFileTypes
}

const uploadData = ref({
  sectionSeparator: '',
  sectionSplitLength: 512,
  sectionSplitByTitle: true,
})

const uploadRef = ref<UploadInst | null>(null)

async function loadConfig() {
  const response = await config()
  publicConfig.value = response.data
  uploadData.value.sectionSplitLength = response.data['config:embedding'].config.maxSectionTokens
}

function getUploadData(): any {
  return uploadData.value
}

function handleUpload() {
  showUploadSettingByUpload.value = true
  showUploadSetting.value = false
  uploadRef.value?.submit()
}

function onUploadChanged(options: { file: UploadFileInfo; fileList: Array<UploadFileInfo>; event?: Event }) {
  if (options.fileList.length > 0) {
    showUploadSettingByUpload.value = false
    showUploadSetting.value = true
  }
}

function onUploadSettingClose() {
  if (!showUploadSettingByUpload.value)
    fileList.value = []
    // uploadRef.value?.clear()
}

onMounted(async () => {
  await Promise.all([loadFileTypes(), loadConfig()])
})
</script>

<template>
  <NUpload
    ref="uploadRef"
    v-model:file-list="fileList"
    :show-file-list="false"
    :action="uploadActionUrl"
    :data="getUploadData()"
    :max="1"
    :on-finish="onFinish"
    :headers="uploadHeaders"
    :default-upload="false"
    @change="onUploadChanged"
  >
    <NUploadDragger>
      <div style="margin-bottom: 12px">
        <NIcon size="48" :depth="3">
          <ArchiveIcon />
        </NIcon>
      </div>
      <NText style="font-size: 16px">
        点击或者拖动文件到该区域来上传
      </NText>
      <NP depth="3" style="margin: 8px 0 0 0">
        压缩文件格式：{{ compressFileTypes.join('、') }}
      </NP>
      <NP depth="3" style="margin: 8px 0 0 0">
        内容文件格式：{{ contentFileTypes.join('、') }}
      </NP>
      <NP v-if="publicConfig" depth="3" style="margin: 8px 0 0 0">
        压缩文件大小&lt;={{ publicConfig['config:embedding'].config.maxCompressedFileSizeText }}，单个文件大小&lt;={{ publicConfig['config:embedding'].config.maxSingleFileSizeText }}，所有文件解压后大小&lt;={{ publicConfig['config:embedding'].config.maxTotalFilesSizeText }}
      </NP>
    </NUploadDragger>
  </NUpload>
  <NModal
    v-model:show="showUploadSetting"
    :mask-closable="false"
    preset="card"
    title="上传参数"
    style="width: 95%; max-width: 480px"
    @close="onUploadSettingClose"
  >
    <NForm
      ref="formRef"
      :model="uploadData"
      label-placement="left"
      label-width="auto"
      require-mark-placement="right-hanging"
    >
      <NFormItem label="分隔符">
        <NInput v-model:value="uploadData.sectionSeparator" placeholder="用于分割段落，支持转义符" />
      </NFormItem>
      <NFormItem label="段落最大长度">
        <NInputNumber v-model:value="uploadData.sectionSplitLength" :max="publicConfig['config:embedding'].config.maxSectionTokens" />
      </NFormItem>
      <NFormItem label="按标题分割段落">
        <NCheckbox v-model:checked="uploadData.sectionSplitByTitle" />
      </NFormItem>
      <div style="display: flex; justify-content: flex-end">
        <NButton round type="primary" @click="handleUpload">
          上传
        </NButton>
      </div>
    </NForm>
  </NModal>
</template>
