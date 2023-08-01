<script lang="ts" setup>
import { computed } from 'vue'
import { NInput, NInputNumber } from 'naive-ui'
import type { EmbeddingChatSetting } from '@/store/modules/embedding'

interface Props {
  setting: EmbeddingChatSetting
}

interface Emit {
  (e: 'update:setting', setting: EmbeddingChatSetting): void
  (e: 'ok'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const setting = computed({
  get() {
    return props.setting
  },
  set(setting: EmbeddingChatSetting) {
    emit('update:setting', setting)
  },
})
</script>

<template>
  <div class="p-4 space-y-5 min-h-[200px]">
    <div class="space-y-6">
      <!-- <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[120px]">{{ $t('setting.role') }}</span>
        <div class="flex-1">
          <NInput v-model:value="systemMessage" type="textarea" :autosize="{ minRows: 1, maxRows: 4 }" />
        </div>
      </div> -->
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">最多携带段落数量</span>
        <div class="flex-1">
          <NInputNumber v-model:value="setting.topSections" min="1" />
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">相似度</span>
        <div class="flex-1">
          <NInputNumber v-model:value="setting.similarity" min="0" max="1" step="0.1" />
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">提示语</span>
        <div class="flex-1">
          <NInput v-model:value="setting.prompt" type="textarea" />
        </div>
      </div>
    </div>
  </div>
</template>
