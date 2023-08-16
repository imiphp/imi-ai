<script setup lang='ts'>
import { computed } from 'vue'
import { NModal } from 'naive-ui'
import Advanced from './Advanced.vue'
import type { ModelConfig } from '@/store'

interface Props {
  visible: boolean
  prompt?: string
  setting: Chat.ChatSetting
  models: { [key: string]: ModelConfig }
  tokens?: number
  payTokens?: number
  readonly?: boolean
}

interface Emit {
  (e: 'update:visible', visible: boolean): void
  (e: 'update:prompt', prompt?: string): void
  (e: 'update:setting', setting: Chat.ChatSetting): void
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const prompt = computed({
  get: () => props.prompt,
  set: (prompt?: string) => emit('update:prompt', prompt),
})

const setting = computed({
  get: () => props.setting,
  set: (setting: Chat.ChatSetting) => emit('update:setting', setting),
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
  <NModal v-model:show="show" :auto-focus="false" preset="card" style="width: 95%; max-width: 660px" title="聊天设置">
    <div v-if="undefined !== props.tokens && undefined !== props.payTokens" class="px-4 space-y-5">
      <div class="flex font-bold">
        <div class="flex flex-1 items-center space-x-4">
          <span class="flex-shrink-0 w-[130px]">累计 Tokens </span>
          <div class="flex-1 text-[#f0a020]">
            {{ props.tokens }}
          </div>
        </div>
        <div class="flex flex-1 items-center space-x-4">
          <span class="flex-shrink-0 w-[130px]">支付 Tokens </span>
          <div class="flex-1 text-[#f0a020]">
            {{ props.payTokens }}
          </div>
        </div>
      </div>
    </div>
    <Advanced v-model:prompt="prompt" v-model:setting="setting" :models="props.models" show-confirm :readonly="readonly" @ok="show = false" />
  </NModal>
</template>
