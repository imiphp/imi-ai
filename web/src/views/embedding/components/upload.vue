<script setup lang='ts'>
import type { UploadFileInfo } from 'naive-ui'
import { NIcon, NP, NText, NUpload, NUploadDragger, useDialog } from 'naive-ui'

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

async function loadFileTypes() {
  const response = await embeddingFileTypes()
  compressFileTypes.value = response.compressFileTypes
  contentFileTypes.value = response.contentFileTypes
}

async function loadConfig() {
  const response = await config()
  publicConfig.value = response.data
}

onMounted(async () => {
  await Promise.all([loadFileTypes(), loadConfig()])
})
</script>

<template>
  <NUpload
    v-model:file-list="fileList"
    :show-file-list="false"
    :action="uploadActionUrl"
    :max="1"
    :on-finish="onFinish"
    :headers="uploadHeaders"
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
</template>
