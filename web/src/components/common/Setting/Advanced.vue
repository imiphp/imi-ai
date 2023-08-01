<script lang="ts" setup>
import { NButton, NRadioButton, NRadioGroup, NSlider } from 'naive-ui'
import { computed, ref } from 'vue'
import type { ChatSetting, ModelConfig } from '@/store'
import { defaultChatSetting } from '@/store'

interface Props {
  setting: ChatSetting
  models: { [key: string]: ModelConfig }
  showConfirm?: boolean
}

interface Emit {
  (e: 'update:setting', setting: ChatSetting): void
  (e: 'ok'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const setting = props.showConfirm
  ? ref({ ...props.setting })
  : computed({
    get() {
      return props.setting
    },
    set(setting: ChatSetting) {
      emit('update:setting', setting)
    },
  })

function ok() {
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
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.model') }} </span>
        <div class="flex-1 overflow-x-auto overflow-y-hidden">
          <NRadioGroup v-model:value="setting.model" name="model">
            <NRadioButton
              v-for="(model, key) of models"
              :key="key"
              :value="key"
              :label="key.toString()"
              :disabled="!model.enable"
            />
          </NRadioGroup>
        </div>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.temperature') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.temperature" :max="2" :min="0" :step="0.1" />
        </div>
        <span>{{ setting.temperature }}</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.top_p') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.top_p" :max="1" :min="0" :step="0.1" />
        </div>
        <span>{{ setting.top_p }}</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.presence_penalty') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.presence_penalty" :max="2" :min="-2" :step="0.1" />
        </div>
        <span>{{ setting.presence_penalty }}</span>
      </div>
      <div class="flex items-center space-x-4">
        <span class="flex-shrink-0 w-[130px]">{{ $t('setting.frequency_penalty') }} </span>
        <div class="flex-1">
          <NSlider v-model:value="setting.frequency_penalty" :max="2" :min="-2" :step="0.1" />
        </div>
        <span>{{ setting.frequency_penalty }}</span>
      </div>
      <div class="flex items-center space-x-4">
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
