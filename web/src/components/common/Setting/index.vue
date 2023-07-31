<script setup lang='ts'>
import { computed } from 'vue'
import { NModal } from 'naive-ui'
import Advanced from './Advanced.vue'
import type { ChatSetting, ModelConfig } from '@/store'

interface Props {
  visible: boolean
  setting: ChatSetting
  models: { [key: string]: ModelConfig }
}

interface Emit {
  (e: 'update:visible', visible: boolean): void
  (e: 'update:setting', setting: ChatSetting): void
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const setting = computed({
  get: () => props.setting,
  set: (setting: ChatSetting) => emit('update:setting', setting),
})

const show = computed({
  get() {
    return props.visible
  },
  set(visible: boolean) {
    emit('update:visible', visible)
  },
})
</script>

<template>
  <NModal v-model:show="show" :auto-focus="false" preset="card" style="width: 95%; max-width: 640px" title="聊天设置">
    <div class="min-h-[100px]">
      <Advanced v-model:setting="setting" :models="props.models" @ok="show = false" />
    </div>
  </NModal>
</template>
