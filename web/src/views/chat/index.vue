<script setup lang='tsx'>
import type { Ref } from 'vue'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { NAlert, NButton, NIcon, NInput, useDialog, useMessage } from 'naive-ui'
import html2canvas from 'html2canvas'
import { DownloadOutline, PaperPlaneSharp, SettingsOutline, StopCircleOutline, TrashOutline } from '@vicons/ionicons5'
import HeaderComponent from '../layout/components/Header/index.vue'
import { Message } from './components'
import { useScroll } from './hooks/useScroll'
import { useChat } from './hooks/useChat'
import { HoverButton, Setting } from '@/components/common'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { QAStatus, defaultChatSetting, useChatStore, useRuntimeStore } from '@/store'
import { config, editSession, fetchChatAPIProcess, getPrompt, getSession, messageList, sendMessage } from '@/api'
import { t } from '@/locales'
import { ChatLayout } from '@/views/chat/layout'
import { decodeSecureField } from '@/utils/request'
import { router } from '@/router'

interface Props {
  usePrompt?: string
}

const props = defineProps<Props>()

let controller = new AbortController()

const route = useRoute()
const dialog = useDialog()
const ms = useMessage()

const chatStore = useChatStore()

const usePrompt = (props.usePrompt === '1' && chatStore.prompt)

const { isMobile } = useBasicLayout()
const { addChat, updateChat, updateChatSome, getChatByUuidAndIndex } = useChat()
const { scrollRef, scrollToBottom, scrollToBottomIfAtBottom } = useScroll()

let { id } = route.params as { id: string }
chatStore.setActive(id ?? '', usePrompt ? chatStore?.prompt : undefined)

const dataSources = computed(() => chatStore.getChatByUuid(id))
const currentChatHistory = computed(() => chatStore.getChatHistoryByCurrentActive)

const inputContent = ref<string>(usePrompt ? (chatStore.prompt?.firstMessageContent ?? '') : '')
const loading = ref<boolean>(false)
const inputRef = ref<Ref | null>(null)

const models = ref([])

const setting = ref(usePrompt ? (chatStore.prompt?.config ?? defaultChatSetting()) : defaultChatSetting())
const showSetting = ref(false)

const runtimeStore = useRuntimeStore()

watch(currentChatHistory, () => {
  runtimeStore.$state.headerTitle = currentChatHistory.value?.title || ''
}, { immediate: true })

let lastMessageId = ''
const messagePageSize = 15
const messageNextPageLoading = ref(false)
const hasMessageNextPage = ref(false)

// 未知原因刷新页面，loading 状态不会重置，手动重置
dataSources.value.forEach((item, index) => {
  if (item.loading)
    updateChatSome(id, index, { loading: false })
})

function handleSubmit() {
  onConversation()
}

async function onConversation() {
  const message = inputContent.value

  if (loading.value)
    return

  if (!message || message.trim() === '')
    return

  controller = new AbortController()

  loading.value = true
  inputContent.value = ''

  const beginTime = parseInt(((new Date()).getTime() / 1000).toString())
  const newSession = !id || id.length === 0
  try {
    // sendMessage
    const sendMessageResponse = await sendMessage(id, message, currentChatHistory.value?.prompt, setting.value)

    id = sendMessageResponse.data.recordId

    if (newSession) {
      chatStore.deleteHistoryById('', false)
      const history = { ...sendMessageResponse.data, isEdit: false }
      history.id = history.recordId
      chatStore.addHistory(history, [], false)
      setting.value = { ...setting.value, ...sendMessageResponse.data.config }
      if (currentChatHistory.value)
        currentChatHistory.value.prompt = sendMessageResponse.data.prompt
    }

    // 用户发送
    addChat(
      id,
      {
        beginTime,
        completeTime: 0,
        message,
        inversion: true,
        error: false,
        conversationOptions: null,
        tokens: sendMessageResponse.chatMessage.tokens,
      },
    )
  }
  catch (error: any) {
    console.error(error)
    const errorMessage = error?.message ?? t('common.wrong')

    if (error.message === 'canceled') {
      updateChatSome(
        id,
        dataSources.value.length - 1,
        {
          message: error.message,
          error: false,
          loading: false,
        },
      )
      scrollToBottomIfAtBottom()
      return
    }

    const currentChat = getChatByUuidAndIndex(id, dataSources.value.length - 1)

    if (currentChat?.message && currentChat.message !== '') {
      updateChatSome(
        id,
        dataSources.value.length - 1,
        {
          message: `${currentChat.message}\n[${errorMessage}]`,
          error: false,
          loading: false,
        },
      )
      return
    }

    updateChat(
      id,
      dataSources.value.length - 1,
      {
        beginTime,
        completeTime: parseInt(((new Date()).getTime() / 1000).toString()),
        message: errorMessage,
        inversion: false,
        error: true,
        loading: false,
        conversationOptions: null,
      },
    )
    scrollToBottomIfAtBottom()
  }
  finally {
    loading.value = false
  }
  if (id && id.length > 0) {
    await fetchStream()
    if (newSession)
      router.replace({ name: 'Chat', params: { id }, state: history.state })
  }
}

async function fetchStream() {
  try {
    loading.value = true
    inputContent.value = ''
    const beginTime = parseInt(((new Date()).getTime() / 1000).toString())
    // AI 返回
    addChat(
      id,
      {
        beginTime,
        completeTime: 0,
        message: '',
        loading: true,
        inversion: false,
        error: false,
        conversationOptions: null,
      },
    )
    scrollToBottom()
    let lastText = ''
    let lastIndex = 0
    const fetchChatAPIOnce = async () => {
      await fetchChatAPIProcess(id, {
        // prompt: message,
        // options,
        signal: controller.signal,
        onDownloadProgress: ({ event }) => {
          const xhr = event.target
          const { responseText }: { responseText: string } = xhr
          // Always process the final line
          let currentIndex = responseText.indexOf('\n\n', lastIndex)
          while (currentIndex > -1) {
            try {
              const chunk = responseText.substring(lastIndex, currentIndex)
              const data = JSON.parse(chunk.substring('data:'.length))
              if (data.content) {
                data.content = decodeSecureField(data.content)
                if (data.content) {
                  updateChatSome(
                    id,
                    dataSources.value.length - 1,
                    {
                      message: lastText += (data.content ?? ''),
                      inversion: false,
                      error: false,
                      loading: true,
                    },
                  )

                  if (data.finishReason === 'length') {
                    lastText = data.content
                    return fetchChatAPIOnce()
                  }
                }

                scrollToBottomIfAtBottom()
              }
              else if (data.message) {
                updateChatSome(
                  id,
                  dataSources.value.length - 1,
                  {
                    message: decodeSecureField(data.message.message),
                    inversion: false,
                    error: false,
                    loading: true,
                    tokens: data.message.tokens,
                  },
                )
              }
            }
            catch (error) {
            //
            }
            lastIndex = currentIndex + 2
            currentIndex = responseText.indexOf('\n\n', lastIndex)
          }
        },
      })
      updateChatSome(id, dataSources.value.length - 1, { loading: false })
    }
    await fetchChatAPIOnce()

    if (usePrompt) {
      chatStore.setActive(id, undefined)
    }
    else {
      const response = await getSession(id)
      chatStore.updateHistory(id, { ...response.data })
    }
  }
  catch (error: any) {
    console.error(error)
    const errorMessage = error?.message ?? t('common.wrong')

    if (error.message === 'canceled') {
      updateChatSome(
        id,
        dataSources.value.length - 1,
        {
          message: error.message,
          error: false,
          loading: false,
        },
      )
      scrollToBottomIfAtBottom()
      return
    }

    const currentChat = getChatByUuidAndIndex(id, dataSources.value.length - 1)

    if (currentChat?.message && currentChat.message !== '') {
      updateChatSome(
        id,
        dataSources.value.length - 1,
        {
          message: `${currentChat.message}\n[${errorMessage}]`,
          error: false,
          loading: false,
        },
      )
      return
    }

    updateChatSome(
      id,
      dataSources.value.length - 1,
      {
        completeTime: parseInt(((new Date()).getTime() / 1000).toString()),
        message: errorMessage,
        inversion: false,
        error: true,
        loading: false,
        conversationOptions: null,
      },
    )
    scrollToBottomIfAtBottom()
  }
  finally {
    loading.value = false
  }
}

function handleExport() {
  if (loading.value)
    return

  const d = dialog.warning({
    title: t('chat.exportImage'),
    content: t('chat.exportImageConfirm'),
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      try {
        d.loading = true
        const ele = document.getElementById('image-wrapper')
        const canvas = await html2canvas(ele as HTMLDivElement, {
          useCORS: true,
        })
        const imgUrl = canvas.toDataURL('image/png')
        const tempLink = document.createElement('a')
        tempLink.style.display = 'none'
        tempLink.href = imgUrl
        tempLink.setAttribute('download', 'chat-shot.png')
        if (typeof tempLink.download === 'undefined')
          tempLink.setAttribute('target', '_blank')

        document.body.appendChild(tempLink)
        tempLink.click()
        document.body.removeChild(tempLink)
        window.URL.revokeObjectURL(imgUrl)
        d.loading = false
        ms.success(t('chat.exportSuccess'))
        Promise.resolve()
      }
      catch (error: any) {
        ms.error(t('chat.exportFailed'))
      }
      finally {
        d.loading = false
      }
    },
  })
}

function handleDelete(index: number) {
  if (loading.value)
    return

  dialog.warning({
    title: t('chat.deleteMessage'),
    content: t('chat.deleteMessageConfirm'),
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: () => {
      chatStore.deleteChatByUuid(id, index)
    },
  })
}

function handleConfig() {
  showSetting.value = true
}

async function handleDeleteSession() {
  if (loading.value)
    return
  dialog.warning({
    title: t('chat.deleteMessage'),
    content: t('chat.deleteMessageConfirm'),
    positiveText: t('common.yes'),
    negativeText: t('common.no'),
    onPositiveClick: async () => {
      const index = chatStore.history.findIndex(item => item.id === id)
      if (index === -1)
        return
      await chatStore.deleteHistory(index)
    },
  })
}

function handleEnter(event: KeyboardEvent) {
  if (!isMobile.value) {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault()
      handleSubmit()
    }
  }
  else {
    if (event.key === 'Enter' && event.ctrlKey) {
      event.preventDefault()
      handleSubmit()
    }
  }
}

function handleStop() {
  if (loading.value) {
    controller.abort()
    loading.value = false
  }
}

const placeholder = computed(() => {
  if (isMobile.value)
    return t('chat.placeholderMobile')
  return t('chat.placeholder')
})

const buttonDisabled = computed(() => {
  return loading.value || !inputContent.value || inputContent.value.trim() === ''
})

const footerClass = computed(() => {
  let classes = ['p-4']
  if (isMobile.value)
    classes = ['sticky', 'left-0', 'bottom-0', 'right-0', 'p-2', 'pr-3', 'overflow-hidden']
  return classes
})

async function saveSetting() {
  if (!id || id.length === 0)
    return

  await editSession({ id, config: setting.value, prompt: currentChatHistory.value?.prompt })
}

async function loadConfig() {
  const response = await config()
  models.value = response.data['config:chat'].config.modelConfigs ?? []
}

async function loadPrompt() {
  if (!usePrompt || !chatStore.prompt?.id)
    return

  const response = await getPrompt(chatStore.prompt.id)
  if (currentChatHistory.value)
    currentChatHistory.value.prompt = response.data.prompt
}

async function handleMessageNextPage() {
  if (!hasMessageNextPage.value)
    return

  messageNextPageLoading.value = true
  try {
    const response = await messageList(id, lastMessageId, messagePageSize)
    for (const item of response.list) {
      const resultItem: Chat.Chat = { ...item }
      resultItem.inversion = item.role === 'user'
      chatStore.addChatByUuid(id, resultItem, 'unshift')
      lastMessageId = item.recordId
    }
    hasMessageNextPage.value = response.hasNextPage
    if (scrollRef.value)
      scrollRef.value.scrollTop = 1
  }
  finally {
    messageNextPageLoading.value = false
  }
}

onMounted(async () => {
  if (scrollRef.value) {
    scrollRef.value.onscroll = () => {
      if (messageNextPageLoading.value)
        return

      const { scrollTop } = (scrollRef.value as HTMLDivElement)
      if (scrollTop <= 0)
        handleMessageNextPage()
    }
  }

  await loadConfig()
  const hasNewSession = chatStore.history && chatStore.history.length > 1 && chatStore.history[0].id.length === 0
  if (!chatStore.history || (chatStore.history.length === 1 && chatStore.history[0].id.length === 0)) {
    await chatStore.loadChatList()
    if (hasNewSession && (!id || id.length === 0)) {
      chatStore.deleteHistoryById('')
      chatStore.addHistory({
        id: '',
        title: 'New Chat',
        isEdit: false,
        createTime: 0,
        updateTime: 0,
        qaStatus: QAStatus.ASK,
        tokens: 0,
      })
    }
  }

  if (id && id.length > 0) {
    lastMessageId = ''
    const [sessionResponse, messageResponse] = await Promise.all([
      getSession(id),
      messageList(id, lastMessageId, messagePageSize),
    ])
    const result: Chat.Chat[] = []
    for (const item of messageResponse.list) {
      const resultItem: Chat.Chat = { ...item }
      resultItem.inversion = item.role === 'user'
      result.push(resultItem)
      lastMessageId = item.recordId
    }
    hasMessageNextPage.value = messageResponse.hasNextPage
    chatStore.updateHistory(id, { ...sessionResponse.data })
    chatStore.setChatsById(id, result.reverse())
    setting.value = { ...setting.value, ...sessionResponse.data.config }
    if (currentChatHistory.value)
      currentChatHistory.value.prompt = sessionResponse.data.prompt
  }
  else {
    if (!currentChatHistory.value) {
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
    }
    if (currentChatHistory.value)
      currentChatHistory.value.prompt = (usePrompt ? (chatStore.prompt?.prompt ?? '') : '')
    await loadPrompt()
  }

  scrollToBottom()

  if (inputRef.value)
    inputRef.value?.focus()
  if (currentChatHistory.value && QAStatus.ANSWER === currentChatHistory.value.qaStatus)
    await fetchStream()
})

onUnmounted(() => {
  if (loading.value)
    controller.abort()
})
</script>

<template>
  <ChatLayout>
    <div class="flex flex-col w-full h-full">
      <HeaderComponent
        v-if="isMobile"
      />
      <main class="flex-1 overflow-hidden">
        <div id="scrollRef" ref="scrollRef" class="h-full overflow-hidden overflow-y-auto">
          <div
            id="image-wrapper"
            class="w-full max-w-screen-xl m-auto dark:bg-[#101014]"
            :class="[isMobile ? 'p-2' : 'p-4']"
          >
            <!-- <template v-if="!dataSources.length">
              <div class="flex items-center justify-center mt-4 text-center text-neutral-300">
                <NIcon :component="Sparkles" size="30" class="mr-2" />
                <span>imi</span>
              </div>
            </template>
            <template v-else> -->
            <div v-if="hasMessageNextPage" class="mb-2">
              <NButton block :loading="messageNextPageLoading" @click="handleMessageNextPage">
                加载更多...
              </NButton>
            </div>
            <div>
              <NAlert v-if="(currentChatHistory?.prompt?.length ?? 0) > 0" :bordered="false" title="提示语 (Prompt)" type="info" class="mb-6 !bg-[#f4f9fe] rounded-md">
                <a class="block hover:text-gray-500" href="javascript:;" @click="handleConfig()">
                  {{ currentChatHistory?.prompt }}
                </a>
              </NAlert>
              <template v-for="(item, index) of dataSources" :key="index">
                <Message
                  :date-time="item.completeTime ? item.completeTime : item.beginTime"
                  :text="item.message"
                  :inversion="item.inversion"
                  :error="item.error"
                  :loading="item.loading"
                  :tokens="item.tokens"
                  @delete="handleDelete(index)"
                />
              </template>
              <div class="sticky bottom-0 left-0 flex justify-center">
                <NButton v-if="loading" type="warning" @click="handleStop">
                  <template #icon>
                    <NIcon :component="StopCircleOutline" size="28" />
                  </template>
                  {{ t('common.stopResponding') }}
                </NButton>
              </div>
            </div>
            <!-- </template> -->
          </div>
        </div>
      </main>
      <footer :class="footerClass">
        <div class="w-full max-w-screen-xl m-auto">
          <div class="flex items-center justify-between space-x-2">
            <HoverButton @click="handleDeleteSession">
              <NIcon class="text-[#4f555e] dark:text-white" :component="TrashOutline" size="20" />
            </HoverButton>
            <HoverButton v-if="!isMobile" @click="handleExport">
              <NIcon class="text-[#4f555e] dark:text-white" :component="DownloadOutline" size="20" />
            </HoverButton>
            <HoverButton @click="handleConfig">
              <NIcon class="text-[#4f555e] dark:text-white" :component="SettingsOutline" size="20" />
            </HoverButton>
            <NInput
              ref="inputRef"
              v-model:value="inputContent"
              type="textarea"
              :placeholder="placeholder"
              :autosize="{ minRows: 1, maxRows: isMobile ? 4 : 8 }"
              @keypress="handleEnter"
            />
            <NButton type="primary" :disabled="buttonDisabled" @click="handleSubmit">
              <template #icon>
                <NIcon class="dark:text-black" :component="PaperPlaneSharp" size="18" />
              </template>
            </NButton>
          </div>
        </div>
      </footer>
    </div>
    <Setting v-if="currentChatHistory" v-model:prompt="currentChatHistory.prompt" v-model:setting="setting" v-model:visible="showSetting" :models="models" :tokens="currentChatHistory?.tokens" :pay-tokens="currentChatHistory?.payTokens" @update:setting="saveSetting" />
  </ChatLayout>
</template>
