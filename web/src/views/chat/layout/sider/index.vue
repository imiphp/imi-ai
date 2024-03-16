<script setup lang='tsx'>
import type { CSSProperties } from 'vue'
import { computed, ref, watch } from 'vue'
import { NButton, NIcon, NLayoutSider, useDialog } from 'naive-ui'
import { TrashOutline } from '@vicons/ionicons5'
import List from './List.vue'
import { QAStatus, useAppStore, useChatStore } from '@/store'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { PromptStore } from '@/components/common'
import { t } from '@/locales'

const appStore = useAppStore()
const chatStore = useChatStore()

const { isMobile } = useBasicLayout()
const show = ref(false)

const collapsed = computed(() => appStore.siderCollapsed)
const dialog = useDialog()

function handleAdd() {
  chatStore.deleteHistoryById('', false)
  chatStore.addHistory({
    id: '',
    title: 'New Chat',
    isEdit: false,
    createTime: 0,
    updateTime: 0,
    qaStatus: QAStatus.ASK,
    tokens: 0,
  })
  if (isMobile.value)
    appStore.setSiderCollapsed(true)
}

function handleClear() {
  dialog.warning({
    title: '询问',
    content: '是否清空历史记录？',
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      chatStore.clearHistory()
    },
  })
}

function handleUpdateCollapsed() {
  appStore.setSiderCollapsed(!collapsed.value)
}

const getMobileClass = computed<CSSProperties>(() => {
  if (isMobile.value) {
    return {
      position: 'fixed',
      zIndex: 50,
    }
  }
  return {}
})

const mobileSafeArea = computed(() => {
  if (isMobile.value) {
    return {
      paddingBottom: 'env(safe-area-inset-bottom)',
    }
  }
  return {}
})

watch(
  isMobile,
  (val) => {
    appStore.setSiderCollapsed(val)
  },
  {
    immediate: true,
    flush: 'post',
  },
)
</script>

<template>
  <NLayoutSider
    :collapsed="collapsed"
    :collapsed-width="0"
    :width="260"
    :show-trigger="isMobile ? false : 'arrow-circle'"
    collapse-mode="transform"
    position="absolute"
    bordered
    :style="getMobileClass"
    @update-collapsed="handleUpdateCollapsed"
  >
    <div class="flex flex-col h-full" :style="mobileSafeArea">
      <main class="flex flex-col flex-1 min-h-0">
        <div class="p-4 flex flex-row">
          <NButton dashed class="flex-1 !mr-1" @click="handleAdd">
            {{ $t('chat.newChatButton') }}
          </NButton>
          <NButton dashed title="清空历史" @click="handleClear">
            <NIcon class="align-middle" :component="TrashOutline" size="14" />
          </NButton>
        </div>
        <div class="flex-1 min-h-0 pb-4 overflow-hidden">
          <List />
        </div>
      </main>
    </div>
  </NLayoutSider>
  <template v-if="isMobile">
    <div v-show="!collapsed" class="fixed inset-0 z-40 w-full h-full bg-black/40" @click="handleUpdateCollapsed" />
  </template>
  <PromptStore v-model:visible="show" />
</template>
