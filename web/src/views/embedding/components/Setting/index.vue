<script setup lang='ts'>
import { computed, ref } from 'vue'
import { NButton, NIcon, NModal, NTabPane, NTabs } from 'naive-ui'
import { BuildOutline, SettingsOutline } from '@vicons/ionicons5'
import General from './General.vue'
import Advanced from '@/components/common/Setting/Advanced.vue'
import type { ModelConfig } from '@/store'
import type { EmbeddingChatSetting } from '@/store/modules/embedding'

interface Props {
  visible: boolean
  embeddingSetting: EmbeddingChatSetting
  setting: Chat.ChatSetting
  models: { [key: string]: ModelConfig }
}

interface Emit {
  (e: 'update:visible', visible: boolean): void
  (e: 'update:embeddingSetting', embeddingSetting: EmbeddingChatSetting): void
  (e: 'update:setting', setting: Chat.ChatSetting): void
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const embeddingSetting = ref(props.embeddingSetting)

const setting = ref(props.setting)

const show = computed({
  get() {
    return props.visible
  },
  set(visible: boolean) {
    emit('update:visible', visible)
  },
})

const active = ref('General')

function ok() {
  emit('update:embeddingSetting', embeddingSetting.value)
  emit('update:setting', setting.value)
  show.value = false
}
</script>

<template>
  <NModal v-model:show="show" :auto-focus="false" preset="card" style="width: 95%; max-width: 660px" title="聊天设置">
    <div>
      <NTabs v-model:value="active" type="line" animated>
        <NTabPane name="General" tab="General">
          <template #tab>
            <NIcon :component="SettingsOutline" size="18" />
            <span class="ml-2">{{ $t('setting.general') }}</span>
          </template>
          <div class="min-h-[100px]">
            <General v-model:setting="embeddingSetting" :models="props.models" @ok="show = false" />
          </div>
        </NTabPane>
        <NTabPane name="Advanced" tab="Advanced">
          <template #tab>
            <NIcon :component="BuildOutline" size="18" />
            <span class="ml-2">{{ $t('setting.advanced') }}</span>
          </template>
          <div class="min-h-[100px]">
            <Advanced v-model:setting="setting" :models="props.models" :show-confirm="false" @ok="show = false" />
          </div>
        </NTabPane>
      </NTabs>
      <div class="text-center">
        <NButton size="small" type="primary" @click="ok()">
          {{ $t('common.save') }}
        </NButton>
      </div>
    </div>
  </NModal>
</template>
