<script setup lang='ts'>
import type { UploadFileInfo } from 'naive-ui'
import { NIcon, NP, NText, NUpload, NUploadDragger, useDialog } from 'naive-ui'

import { ArchiveOutline as ArchiveIcon } from '@vicons/ionicons5'
import { nextTick, ref } from 'vue'
import { useRouter } from 'vue-router'
import service from '@/utils/request/axios'
import { useAuthStore } from '@/store'

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
        文件格式：zip/rar/7z/xz/gz/bz/txt/md，文件大小限制：4M
      </NP>
    </NUploadDragger>
  </NUpload>
</template>
