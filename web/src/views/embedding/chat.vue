<script setup lang='tsx'>
import { NBreadcrumb, NBreadcrumbItem, NButton, NCard, NDivider, NIcon, NInput, NLayout, NLayoutContent, NLayoutSider, NSpin, useDialog, useMessage } from 'naive-ui'
import type { CSSProperties } from 'vue'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import html2canvas from 'html2canvas'
import { DownloadOutline, PaperPlaneSharp, RefreshSharp, SettingsOutline, Sparkles, StopCircleOutline } from '@vicons/ionicons5'
import HeaderComponent from '../layout/components/Header/index.vue'
import { Message } from '../chat/components'
import { useScroll } from '../chat/hooks/useScroll'
import { Setting } from './components'
import { chatList, config, fetchEmbeddingChatAPIProcess, getProject, sectionList, sendEmbeddingMessage } from '@/api'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { defaultChatSetting, useAppStore, useRuntimeStore } from '@/store'
import { defaultEmbeddingSetting, useEmbeddingStore } from '@/store/modules/embedding'
import { t } from '@/locales'
import { HoverButton, Time } from '@/components/common'
import { decodeSecureField } from '@/utils/request'

const { scrollRef, scrollToBottom, scrollToBottomIfAtBottom } = useScroll()
const appStore = useAppStore()
const route = useRoute()
const dialog = useDialog()
const ms = useMessage()
const embeddingState = useEmbeddingStore()
const runtimeStore = useRuntimeStore()
const id = route.params.id.toString()
const selectedKeys = ref<Array<string | number>>([])
const selectedFileId = computed(() => selectedKeys.value[0])
const sectionListData = ref<Array<Embedding.Section>>([])
const showLoading = ref(false)
const dataSources = ref<Array<Chat.Chat>>([])
const loading = ref<boolean>(false)
const buttonDisabled = computed(() => {
  return loading.value
})
const prompt = ref<string>('')
const currentChatReply = ref<Chat.Chat | null>(null)
let qaId = ''

const models = ref([])
const embeddingSetting = ref(defaultEmbeddingSetting())
const setting = ref(defaultChatSetting())
const showSetting = ref(false)

const { isMobile } = useBasicLayout()

const placeholder = computed(() => {
  if (isMobile.value)
    return t('chat.placeholderMobile')
  return t('chat.placeholder')
})

const collapsed = computed(() => appStore.siderCollapsed)

const getMobileClass = computed<CSSProperties>(() => {
  if (isMobile.value) {
    return {
      position: 'fixed',
      zIndex: 50,
    }
  }
  return {}
})

const getContainerClass = computed(() => {
  return [
    'h-full',
    { 'pl-[260px]': !isMobile.value && !collapsed.value },
  ]
})

let lastMessageId = ''
const messagePageSize = 15
const messageNextPageLoading = ref(false)
const hasMessageNextPage = ref(false)

function handleUpdateCollapsed() {
  appStore.setSiderCollapsed(!collapsed.value)
}

async function loadSectionList() {
  try {
    showLoading.value = true
    const response = await sectionList(id, selectedFileId.value.toString())
    sectionListData.value = response.list
  }
  finally {
    showLoading.value = false
  }
}

watch(selectedFileId, async () => {
  await loadSectionList()
})

let controller = new AbortController()

function handleStop() {
  if (loading.value) {
    controller.abort()
    loading.value = false
  }
}

const footerClass = computed(() => {
  let classes = ['p-4']
  if (isMobile.value)
    classes = ['sticky', 'left-0', 'bottom-0', 'right-0', 'p-2', 'pr-3', 'overflow-hidden']
  return classes
})

function handleSubmit() {
  onConversation()
}

async function onConversation() {
  const message = prompt.value

  if (loading.value)
    return

  if (!message || message.trim() === '')
    return

  controller = new AbortController()

  loading.value = true
  prompt.value = ''

  const beginTime = parseInt(((new Date()).getTime() / 1000).toString())
  try {
    // sendMessage
    const sendMessageResponse = await sendEmbeddingMessage(id, message, setting.value, embeddingSetting.value.similarity, embeddingSetting.value.topSections, embeddingSetting.value.prompt)
    qaId = sendMessageResponse.data.recordId

    const beginTime = parseInt((sendMessageResponse.data.createTime / 1000).toString())

    dataSources.value.push({
      beginTime,
      completeTime: beginTime,
      message,
      inversion: true,
      error: false,
      loading: false,
      conversationOptions: null,
    })
    currentChatReply.value = {
      beginTime,
      completeTime: beginTime,
      message: '',
      inversion: false,
      error: false,
      loading: false,
      conversationOptions: null,
    }
    dataSources.value.push(currentChatReply.value)
  }
  catch (error: any) {
    console.error(error)
    const errorMessage = error?.message ?? t('common.wrong')

    if (error.message === 'canceled') {
      currentChatReply.value = {
        beginTime,
        completeTime: beginTime,
        message: errorMessage,
        inversion: false,
        error: false,
        loading: false,
        conversationOptions: null,
      }
      dataSources.value.push(currentChatReply.value)
      scrollToBottomIfAtBottom()
      return
    }

    scrollToBottomIfAtBottom()
    return
  }
  finally {
    loading.value = false
  }
  await fetchStream()
}

async function fetchStream() {
  try {
    loading.value = true
    scrollToBottom()
    let lastText = ''
    let lastIndex = 0
    const fetchChatAPIOnce = async () => {
      await fetchEmbeddingChatAPIProcess(qaId, {
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
                  if (currentChatReply.value)
                    currentChatReply.value.message = lastText += (data.content ?? '')
                }
                if (data.finishReason === 'length') {
                  lastText = data.content
                  return fetchChatAPIOnce()
                }
                scrollToBottomIfAtBottom()
              }
              else if (data.message) {
                if (currentChatReply.value)
                  currentChatReply.value.tokens = data.message.tokens
              }
              if (data.finishReason === 'error' && currentChatReply.value)
                currentChatReply.value.error = true
            }
            catch (error) {
            //
            }
            lastIndex = currentIndex + 2
            currentIndex = responseText.indexOf('\n\n', lastIndex)
          }
        },
      })
      if (currentChatReply.value) {
        currentChatReply.value.completeTime = parseInt(((new Date()).getTime() / 1000).toString())
        currentChatReply.value.loading = false
      }
    }
    await fetchChatAPIOnce()
  }
  catch (error: any) {
    console.error(error)
    const errorMessage = error?.message ?? t('common.wrong')

    if (error.message === 'canceled') {
      if (currentChatReply.value) {
        currentChatReply.value.message = error.message
        currentChatReply.value.loading = false
      }
      scrollToBottomIfAtBottom()
      return
    }

    if (currentChatReply.value) {
      currentChatReply.value.completeTime = parseInt(((new Date()).getTime() / 1000).toString())
      currentChatReply.value.message += `\n[${errorMessage}]`
      currentChatReply.value.error = true
      currentChatReply.value.loading = false
    }
    scrollToBottomIfAtBottom()
  }
  finally {
    loading.value = false
  }
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

function handleConfig() {
  showSetting.value = true
}

async function loadConfig() {
  const response = await config()
  models.value = response.data['config:embedding'].config.chatModelConfigs ?? []
}

async function handleMessageNextPage() {
  if (!hasMessageNextPage.value)
    return

  messageNextPageLoading.value = true
  try {
    const response = await chatList(id, lastMessageId, messagePageSize)
    for (const item of response.list) {
      const resultItem: Chat.Chat = { ...item }
      resultItem.inversion = item.role === 'user'
      dataSources.value.unshift({
        beginTime: item.createTime / 1000,
        completeTime: item.completeTime / 1000,
        message: item.answer,
        inversion: false,
        error: false,
        loading: false,
        conversationOptions: null,
        tokens: item.tokens,
      })
      dataSources.value.unshift({
        beginTime: item.createTime / 1000,
        completeTime: item.createTime / 1000,
        message: item.question,
        inversion: true,
        error: false,
        loading: false,
        conversationOptions: null,
        tokens: item.tokens,
      })
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

function retry() {
  if (currentChatReply.value) {
    Object.assign(currentChatReply.value, {
      message: '',
      inversion: false,
      error: false,
      loading: true,
    })
  }

  fetchStream()
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

  let item
  try {
    showLoading.value = true
    await loadConfig()
    lastMessageId = ''
    const [projectResponse, chatListPromiseResponse] = await Promise.all([getProject(id), chatList(id, lastMessageId, messagePageSize)])
    embeddingState.$state.currentProject = projectResponse.data
    runtimeStore.$state.headerTitle = projectResponse.data.name
    const _dataSources: Array<Chat.Chat> = []
    chatListPromiseResponse.list.reverse()
    for (item of chatListPromiseResponse.list) {
      _dataSources.push({
        beginTime: item.createTime / 1000,
        completeTime: item.createTime / 1000,
        message: item.question,
        inversion: true,
        error: false,
        loading: false,
        conversationOptions: null,
        tokens: item.tokens,
      })
      currentChatReply.value = {
        beginTime: item.createTime / 1000,
        completeTime: item.completeTime / 1000,
        message: item.answer,
        inversion: false,
        error: false,
        loading: false,
        conversationOptions: null,
        tokens: item.tokens,
      }
      hasMessageNextPage.value = chatListPromiseResponse.hasNextPage
      _dataSources.push(currentChatReply.value)
      qaId = item.recordId
      lastMessageId = item.recordId
    }
    setting.value = { ...setting.value, ...projectResponse.data.chatConfig }
    if (item) {
      setting.value = { ...setting.value, ...item.config }
      embeddingSetting.value = { ...embeddingSetting.value, topSections: item.topSections, similarity: item.similarity, prompt: item.prompt }
    }
    else {
      embeddingSetting.value = {
        topSections: projectResponse.data.topSections,
        similarity: projectResponse.data.similarity,
        prompt: projectResponse.data.prompt,
      }
    }
    dataSources.value = _dataSources
    scrollToBottom()
  }
  finally {
    showLoading.value = false
  }
  if (qaId.length > 0 && item?.completeTime === 0)
    await fetchStream()
})
</script>

<template>
  <NSpin v-show="showLoading" class="spin-loading" />
  <NCard :bordered="false" class="h-full" content-style="padding:0;height: 100%;">
    <div class="flex flex-col w-full h-full">
      <HeaderComponent
        v-if="isMobile"
      />
      <div v-if="!isMobile">
        <NBreadcrumb class="!leading-[24px]">
          <NBreadcrumbItem>
            <RouterLink to="/">
              首页
            </RouterLink>
          </NBreadcrumbItem>
          <NBreadcrumbItem>
            <RouterLink to="/embedding">
              模型训练 (RAG)
            </RouterLink>
          </NBreadcrumbItem>
          <NBreadcrumbItem>
            <RouterLink v-if="embeddingState.$state.currentProject" :to="{ name: 'ViewEmbeddingProject', params: { id: embeddingState.$state.currentProject.recordId } }">
              <span v-text="embeddingState.$state.currentProject.name || '加载中'" />
            </RouterLink>
            <span v-else>加载中</span>
          </nbreadcrumbitem>
          <NBreadcrumbItem>
            AI聊天
          </NBreadcrumbItem>
        </NBreadcrumb>
        <NDivider class="!mt-[2px] !mb-[2px]" />
      </div>
      <NLayout class="z-40 transition h-full" has-sider>
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
          <div class="p-2 leading-7">
            <div span="4 m:2 l:1">
              <p><b>ID：</b><span v-text="embeddingState.$state.currentProject?.recordId" /></p>
            </div>
            <div span="4 m:2 l:1">
              <p><b>名称：</b><span v-text="embeddingState.$state.currentProject?.name" /></p>
            </div>
            <div span="4 m:2 l:1">
              <p><b>状态：</b><span v-text="embeddingState.$state.currentProject?.statusText" /></p>
            </div>
            <div span="4 m:2 l:1">
              <p><b>Token 数量：</b><span v-text="embeddingState.$state.currentProject?.tokens" /></p>
            </div>
            <div span="4 m:2 l:1">
              <p><b>创建时间：</b><Time :time="embeddingState.$state.currentProject?.createTime" /></p>
            </div>
            <div span="4 m:2 l:1">
              <p><b>权限：</b><span v-text="embeddingState.$state.currentProject?.public ? '公开' : '私有'" /></p>
            </div>
          </div>
        </NLayoutSider>
        <NLayoutContent :class="getContainerClass">
          <div class="flex flex-col w-full h-full">
            <main class="flex-1 overflow-hidden">
              <div id="scrollRef" ref="scrollRef" class="h-full overflow-hidden overflow-y-auto">
                <div v-if="hasMessageNextPage" class="mb-2">
                  <NButton block :loading="messageNextPageLoading" @click="handleMessageNextPage">
                    加载更多...
                  </NButton>
                </div>
                <div
                  id="image-wrapper"
                  class="w-full max-w-screen-xl m-auto dark:bg-[#101014]"
                  :class="[isMobile ? 'p-2' : 'p-4']"
                >
                  <template v-if="!dataSources.length">
                    <div class="flex items-center justify-center mt-4 text-center text-neutral-300">
                      <NIcon :component="Sparkles" size="30" class="mr-2" />
                      <span>imi</span>
                    </div>
                  </template>
                  <template v-else>
                    <template
                      v-for="item of dataSources"
                      :key="item.beginTime"
                    >
                      <Message
                        :date-time="item.completeTime > 0 ? item.completeTime : item.beginTime"
                        :text="item.message"
                        :inversion="item.inversion"
                        :error="item.error"
                        :loading="item.loading"
                        :tokens="item.inversion ? undefined : item.tokens"
                        token-prefix="消耗"
                      />
                      <div v-if="item.error" class="text-center mt-1">
                        <NButton @click="retry">
                          <NIcon :component="RefreshSharp" size="20" />
                          重试
                        </NButton>
                      </div>
                    </template>
                    <div class="sticky bottom-0 left-0 flex justify-center">
                      <NButton v-if="loading" type="warning" @click="handleStop">
                        <template #icon>
                          <NIcon :component="StopCircleOutline" size="28" />
                        </template>
                        {{ t('common.stopResponding') }}
                      </NButton>
                    </div>
                  </template>
                </div>
              </div>
            </main>
            <footer :class="footerClass">
              <div class="w-full max-w-screen-xl m-auto">
                <div class="flex items-center justify-between space-x-2">
                  <!-- <HoverButton @click="handleDeleteSession">
                    <span class="text-xl text-[#4f555e] dark:text-white">
                      <SvgIcon icon="ri:delete-bin-line" />
                    </span>
                  </HoverButton> -->
                  <HoverButton @click="handleExport">
                    <NIcon class="text-[#4f555e] dark:text-white" :component="DownloadOutline" size="20" />
                  </HoverButton>
                  <HoverButton @click="handleConfig">
                    <NIcon class="text-[#4f555e] dark:text-white" :component="SettingsOutline" size="20" />
                  </HoverButton>
                  <NInput
                    ref="inputRef"
                    v-model:value="prompt"
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
        </NLayoutContent>
      </NLayout>
    </div>
  </NCard>
  <template v-if="isMobile">
    <div v-show="!collapsed" class="fixed inset-0 z-40 w-full h-full bg-black/40" @click="handleUpdateCollapsed" />
  </template>
  <Setting v-if="showSetting" v-model:setting="setting" v-model:embedding-setting="embeddingSetting" v-model:visible="showSetting" :models="models" />
</template>
