<script lang="ts" setup>
import { NButton, NInput, NSelect, NSlider } from 'naive-ui'
import { computed, ref } from 'vue'
import type { ModelConfig } from '@/store'
import { defaultChatSetting } from '@/store'
import { useBasicLayout } from '@/hooks/useBasicLayout'

interface Props {
  prompt?: string
  setting: Chat.ChatSetting
  models: ModelConfig[]
  showConfirm?: boolean
  readonly?: boolean
}

interface Emit {
  (e: 'update:prompt', prompt: string): void
  (e: 'update:setting', setting: Chat.ChatSetting): void
  (e: 'ok'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const { isMobile } = useBasicLayout()

const prompt = props.showConfirm
  ? ref(props.prompt ?? '')
  : computed({
    get() {
      return props.prompt ?? ''
    },
    set(prompt: string) {
      emit('update:prompt', prompt)
    },
  })

const setting = props.showConfirm
  ? ref({ ...props.setting })
  : computed({
    get() {
      return props.setting
    },
    set(setting: Chat.ChatSetting) {
      emit('update:setting', setting)
    },
  })

const model = computed(() => {
  for (const item of props.models) {
    if (item.model === setting.value.model)
      return item
  }
  return null
})

const modelsSelectOptions = computed(() => {
  return props.models.map((item) => {
    return {
      label: item.title.length === 0 ? item.model : item.title,
      value: item.model,
      disabled: !item.enable,
    }
  })
})

function ok() {
  if (undefined !== props.prompt)
    emit('update:prompt', prompt.value ?? '')
  emit('update:setting', setting.value)
  emit('ok')
}

function handleReset() {
  setting.value = defaultChatSetting()
}
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
      <div class="items-center space-x-4" :class="isMobile ? [] : ['flex']">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.model') }} </span>
        <div class="flex-1" :class="isMobile ? ['!ml-0 mt-2'] : []">
          <NSelect v-model:value="setting.model" :options="modelsSelectOptions" />
        </div>
      </div>
      <div v-if="model" class="leading-10 !mt-2">
        <p><b>费用：</b>输入倍率-<span class="text-[#f0a020] font-bold">{{ model.inputTokenMultiple }}</span>，输出倍率-<span class="text-[#f0a020] font-bold">{{ model.outputTokenMultiple }}</span></p>
        <p v-show="model.tips.length > 0">
          <b>提示：</b>{{ model.tips }}
        </p>
      </div>
      <div v-if="undefined !== props.prompt" class="items-center space-x-4" :class="isMobile ? [] : ['flex']">
        <span class="flex-shrink-0 w-[130px]">提示语</span>
        <div class="flex-1" :class="isMobile ? ['!ml-0 mt-2'] : []">
          <NInput v-model:value="prompt" type="textarea" :readonly="readonly" rows="5" />
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.temperature') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.temperature" :max="2" :min="0" :step="0.1" :disabled="readonly" />
        </div>
        <span>{{ setting.temperature }}</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.top_p') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.top_p" :max="1" :min="0" :step="0.1" :disabled="readonly" />
        </div>
        <span>{{ setting.top_p }}</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.presence_penalty') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.presence_penalty" :max="2" :min="-2" :step="0.1" :disabled="readonly" />
        </div>
        <span>{{ setting.presence_penalty }}</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.frequency_penalty') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.frequency_penalty" :max="2" :min="-2" :step="0.1" :disabled="readonly" />
        </div>
        <span>{{ setting.frequency_penalty }}</span>
      </div>
      <div v-if="!readonly" class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">&nbsp;</span>
        <NButton v-show="props.showConfirm" size="small" type="primary" @click="ok()">
          {{ $t('common.save') }}
        </NButton>
        <NButton size="small" @click="handleReset">
          {{ $t('common.reset') }}
        </NButton>
      </div>
    </div>
  </div>
</template>
