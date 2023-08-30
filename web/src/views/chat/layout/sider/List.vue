<script setup lang='ts'>
import { computed, onMounted, ref } from 'vue'
import type { ScrollbarInst } from 'naive-ui'
import { NButton, NIcon, NInput, NPopconfirm, NScrollbar } from 'naive-ui'
import { ChatboxEllipsesOutline, ChatbubblesSharp, PencilOutline, SaveOutline, TrashOutline } from '@vicons/ionicons5'
import { useAppStore, useChatStore } from '@/store'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { debounce } from '@/utils/functions/debounce'
import { editSession } from '@/api'

const { isMobile } = useBasicLayout()

const appStore = useAppStore()
const chatStore = useChatStore()

const dataSources = computed(() => chatStore.history)

const nextPageLoading = ref(false)

const scrollBar = ref<ScrollbarInst | null>(null)

let beforeEditHistory: Chat.History | null = null

async function handleSelect({ id }: Chat.History) {
  if (isActive(id))
    return
  // 接口加载数据
  if (!id || id === '')
    return

  chatStore.deleteHistoryById('')

  if (chatStore.active) {
    const data: Partial<Chat.History> = { isEdit: false }
    if (beforeEditHistory?.id === chatStore.active)
      data.title = beforeEditHistory.title

    beforeEditHistory = null
    chatStore.updateHistory(chatStore.active, data)
  }
  await chatStore.setActive(id)

  if (isMobile.value)
    appStore.setSiderCollapsed(true)
}

async function handleEdit({ id }: Chat.History, isEdit: boolean, event?: MouseEvent) {
  event?.stopPropagation()
  chatStore.updateHistory(id, { isEdit })
  if (isEdit) {
    const history = chatStore.history.find(item => item.id === id)
    if (history)
      beforeEditHistory = { ...history }
  }
  else {
    if (!beforeEditHistory)
      return

    const history = chatStore.history.find(item => item.id === id)
    if (!history) {
      beforeEditHistory = null
      return
    }
    if (beforeEditHistory.title !== history.title) {
      // 更新接口
      await editSession({ id: history.id, title: history.title })
    }
    beforeEditHistory = null
  }
}

function handleDelete(index: number, event?: MouseEvent | TouchEvent) {
  event?.stopPropagation()
  chatStore.deleteHistory(index)
  if (isMobile.value)
    appStore.setSiderCollapsed(true)
}

const handleDeleteDebounce = debounce(handleDelete, 600)

function handleEnter(chat: Chat.History, isEdit: boolean, event: KeyboardEvent) {
  event?.stopPropagation()
  if (event.key === 'Enter')
    handleEdit(chat, isEdit)
}

function isActive(id: string) {
  return chatStore.active === id
}

function onScroll(e: Event) {
  if (nextPageLoading.value || !chatStore.hasNextPage)
    return
  const { scrollTop, scrollHeight, clientHeight } = (e.target as HTMLElement)
  if (scrollHeight > 0 && scrollTop + clientHeight >= scrollHeight - 10)
    handleNextPage()
}

async function handleNextPage() {
  nextPageLoading.value = true
  try {
    const page = chatStore.page
    await chatStore.loadListNextPage()
    chatStore.hasNextPage = chatStore.page !== page
  }
  finally {
    nextPageLoading.value = false
  }
}

onMounted(() => {
  if (scrollBar.value) {
    const element = document.querySelector<HTMLElement>(`.session-item[data-id="${chatStore.active}"]`)
    if (element)
      scrollBar.value.scrollTo({ top: element.offsetTop - window.innerHeight * (1 - 0.618) })
  }
})
</script>

<template>
  <NScrollbar ref="scrollBar" class="px-4" :on-scroll="onScroll">
    <div class="flex flex-col gap-2 text-sm">
      <template v-if="!dataSources.length">
        <div class="flex flex-col items-center mt-4 text-center text-neutral-300">
          <NIcon class="mb-2" :component="ChatbubblesSharp" size="30" />
          <span>{{ $t('common.noData') }}</span>
        </div>
      </template>
      <template v-else>
        <div v-for="(item, index) of dataSources" :key="index" class="session-item" :data-id="item.id">
          <a
            class="relative flex items-center gap-3 px-3 py-3 break-all border rounded-md cursor-pointer hover:bg-neutral-100 group dark:border-neutral-800 dark:hover:bg-[#24272e]"
            :class="isActive(item.id) && ['border-[#4b9e5f]', 'bg-neutral-100', 'text-[#4b9e5f]', 'dark:bg-[#24272e]', 'dark:border-[#4b9e5f]', 'pr-14']"
            @click="handleSelect(item)"
          >
            <NIcon class="!scale-x-[-1]" :component="ChatboxEllipsesOutline" size="14" />
            <div class="relative flex-1 overflow-hidden break-all text-ellipsis whitespace-nowrap">
              <NInput
                v-if="item.isEdit"
                v-model:value="item.title" size="tiny"
                @keypress="handleEnter(item, false, $event)"
              />
              <span v-else>{{ item.title }}</span>
            </div>
            <div v-if="'' !== item.id && isActive(item.id)" class="absolute z-10 flex visible right-1">
              <template v-if="item.isEdit">
                <button class="p-1" @click="handleEdit(item, false, $event)">
                  <NIcon class="align-middle" :component="SaveOutline" size="14" />
                </button>
              </template>
              <template v-else>
                <button class="p-1" @click="handleEdit(item, true, $event)">
                  <NIcon class="align-middle border-b-[1px] border-b-[#4b9e5f] top-[-1px]" :component="PencilOutline" size="14" />
                </button>
                <NPopconfirm placement="bottom" @positive-click="handleDeleteDebounce(index, $event)">
                  <template #trigger>
                    <button class="p-1">
                      <NIcon class="align-middle" :component="TrashOutline" size="14" />
                    </button>
                  </template>
                  {{ $t('chat.deleteHistoryConfirm') }}
                </NPopconfirm>
              </template>
            </div>
          </a>
        </div>
        <div v-if="chatStore.hasNextPage">
          <NButton block :loading="nextPageLoading" @click="handleNextPage">
            加载更多...
          </NButton>
        </div>
      </template>
    </div>
  </NScrollbar>
</template>
