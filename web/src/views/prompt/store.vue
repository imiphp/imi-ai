<script setup lang='ts'>
import type { FormInst } from 'naive-ui'
import { NBreadcrumb, NBreadcrumbItem, NButton, NCard, NCheckbox, NCheckboxGroup, NEllipsis, NEmpty, NForm, NFormItemRow, NGi, NGrid, NInput, NModal, NPagination, NRadio, NRadioGroup, NSelect, NSpace, NSpin, NSwitch, NTabPane, NTabs, NTag, useMessage } from 'naive-ui'

import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { config, convertPromptFormToChat, fetchChatAPIProcess, promptList as getPromptList, promptCategoryList, submitPromptForm } from '@/api'
import { FormItemType, defaultChatSetting, useChatStore } from '@/store'
import { SettingAdvanced } from '@/components/common'
import { decodeSecureField } from '@/utils/request'
import { t } from '@/locales'

const message = useMessage()
const chatStore = useChatStore()
const router = useRouter()

const controller = new AbortController()

const showLoading = ref(false)
const categoryList = ref<any[]>([])
const categoryId = ref('')
watch(categoryId, () => {
  loadPromptList()
})
const search = ref('')
const promptList = ref<any[]>([])
const showPromptData = ref<any>(null)
const form = ref<FormInst | null>(null)
const formData = ref<any>(null)
const formRules = ref<any>({})
const formLoading = ref(false)
const formSession = ref<any>({})
const formStreamContent = ref('')
const formPrompt = computed(() => {
  if (!formData.value || !showPromptData.value)
    return ''

  return parsePrompt(showPromptData.value.prompt)
})
const firstMessageContent = computed(() => {
  if (!formData.value || !showPromptData.value)
    return ''

  return parsePrompt(showPromptData.value.firstMessageContent)
})
const activedPromptTab = ref('')

const setting = ref<any>({})
const models = ref({})

const page = ref(1)
const pageSize = ref(15)
const pageCount = ref(1)

async function onUpdateChange(_page: number) {
  page.value = _page
  await loadPromptList()
}

async function loadConfig() {
  const response = await config()
  models.value = response.data['config:chat'].config.modelConfig ?? []
}

async function loadCategoryList() {
  const response = await promptCategoryList()
  categoryList.value = response.list
}

async function loadPromptList(loading = true) {
  try {
    if (loading)
      showLoading.value = true
    const response = await getPromptList(
      categoryId.value,
      search.value,
      page.value, pageSize.value,
    )
    promptList.value = response.list
    pageCount.value = response.pageCount
  }
  finally {
    if (loading)
      showLoading.value = false
  }
}

function showPrompt(item: any) {
  const _formRules: any = {}
  if (item.formConfig) {
    const _formData: any = {}
    for (const formItem of item.formConfig) {
      if (formItem.type === FormItemType.SWITCH)
        _formData[formItem.id] = formItem.default ? (formItem.checkedValue ?? true) : (formItem.uncheckedValue ?? false)
      else
        _formData[formItem.id] = formItem.default

      if (formItem.required) {
        _formRules[formItem.id] = [
          { required: true, message: '必填' },
        ]
      }
    }
    formData.value = _formData
    activedPromptTab.value = 'form'
  }
  else {
    formData.value = null
    activedPromptTab.value = 'prompt'
  }
  formRules.value = _formRules
  const _setting = { ...defaultChatSetting(), ...(item.config ?? {}) }
  delete _setting.prompt
  setting.value = _setting

  showPromptData.value = { ...item }
}

async function submitForm() {
  try {
    formLoading.value = true
    formStreamContent.value = ''
    await form.value?.validate().then(async () => {
      const response = await submitPromptForm(showPromptData.value.recordId, formData.value)
      formSession.value = response.data
      await fetchStream()
    })
  }
  finally {
    formLoading.value = false
  }
}

async function fetchStream() {
  try {
    let lastIndex = 0
    const fetchChatAPIOnce = async () => {
      await fetchChatAPIProcess(formSession.value.recordId, {
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
                  formStreamContent.value += (data.content ?? '')

                  if (data.finishReason === 'length')
                    return fetchChatAPIOnce()
                }
              }
              else if (data.message) {
                formStreamContent.value = decodeSecureField(data.message.message)
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
    }
    await fetchChatAPIOnce()
  }
  catch (error: any) {
    console.error(error)
    const errorMessage = error?.message ?? t('common.wrong')

    if (error.message === 'canceled') {
      formStreamContent.value = error.message
      return
    }

    formStreamContent.value += `\n[${errorMessage}]`
  }
}

async function convertToChat() {
  await convertPromptFormToChat(formSession.value.recordId)
  message.success('转换成功')
  router.push({
    name: 'Chat',
    params: {
      id: formSession.value.recordId,
    },
  })
}

function createSession() {
  const prompt: Chat.ChatStatePrompt = {
    config: setting.value,
  }
  if (activedPromptTab.value === 'form') {
    prompt.prompt = formPrompt.value
    prompt.firstMessageContent = firstMessageContent.value
  }
  else {
    prompt.id = showPromptData.value.recordId
  }

  chatStore.prompt = prompt
  router.push({
    name: 'Chat',
    query: {
      usePrompt: 1,
    },
  })
}

function parsePrompt(prompt: string) {
  for (const key in formData.value)
    prompt = prompt.replace(new RegExp(`{${key}}`, 'g'), formData.value[key] ?? '')

  return prompt
}

onMounted(async () => {
  try {
    showLoading.value = true
    await Promise.all([
      loadConfig(),
      loadCategoryList(),
      loadPromptList(false),
    ])
  }
  finally {
    showLoading.value = false
  }
})
</script>

<template>
  <div class="wrap">
    <!-- 面包屑 -->
    <NBreadcrumb class="!leading-[24px]">
      <NBreadcrumbItem>
        <RouterLink to="/">
          首页
        </RouterLink>
      </NBreadcrumbItem>
      <NBreadcrumbItem>
        模型市场
      </NBreadcrumbItem>
    </NBreadcrumb>
    <NSpin :show="showLoading">
      <!-- 提示语分类 -->
      <NSpace class="mt-2" align="center">
        <NTag :checked="categoryId === ''" checkable @click="categoryId = ''">
          全部
        </NTag>
        <NTag v-for="(item, index) of categoryList" :key="index" :checked="categoryId === item.recordId" checkable @click="page = 1;categoryId = item.recordId">
          {{ item.title }}
        </NTag>
        <NInput
          v-model:value="search" clearable placeholder="关键词搜索" @keypress.enter="page = 1;loadPromptList()" @clear="nextTick(() => {
            page = 1;loadPromptList()
          })"
        />
        <NButton @click="loadPromptList()">
          搜索
        </NButton>
      </NSpace>
      <!-- 提示语列表 -->
      <div class="mt-2">
        <NGrid v-if="promptList.length > 0" x-gap="12" y-gap="16" cols="1 s:2 l:3" item-responsive responsive="screen">
          <NGi v-for="(item, index) of promptList" :key="index">
            <a class="block hover:!text-gray-500" href="javascript:;" @click="showPrompt(item)">
              <NCard embedded class="prompt-list-card">
                <template #header>
                  {{ item.title }}
                </template>
                <NEllipsis :line-clamp="8" :tooltip="false">
                  <p v-text="item.prompt" />
                </NEllipsis>
              </NCard>
            </a>
          </NGi>
        </NGrid>
        <NEmpty v-else />
      </div>
      <NPagination v-model:page="page" class="mt-4 float-right" :page-count="pageCount" :on-update:page="onUpdateChange" />
    </NSpin>
  </div>
  <!-- 提示语弹窗 -->
  <NModal
    :show="!!showPromptData"
    preset="card"
    :title="showPromptData?.title"
    style="width: 720px; max-width: 100vw; max-height: 100vh"
    mask-closable
    content-style="overflow: auto"
    @update-show="(show) => { showPromptData = show ? showPromptData : null }"
    @close="showPromptData = null"
  >
    <NTabs v-model:value="activedPromptTab" animated>
      <NTabPane v-if="showPromptData?.formConfig" name="form" tab="表单">
        <NSpin :show="formLoading">
          <NForm ref="form" :model="formData" :rules="formRules">
            <NFormItemRow v-for="(item, index) of showPromptData.formConfig" :key="index" :path="item.id" :label="`${item.label}：`" :label-placement="FormItemType.SWITCH === item.type ? 'left' : 'top'">
              <!-- 下拉 -->
              <NSelect v-if="FormItemType.SELECT === item.type" v-model:value="formData[item.id]" :options="item.data" filterable tag />
              <!-- 多行文本 -->
              <NInput v-else-if="FormItemType.TEXTAREA === item.type" v-model:value="formData[item.id]" type="textarea" :rows="item.rows" :autosize="undefined === item.autosize ? (undefined !== item.minRows || undefined !== item.maxRows ? { minRows: item.minRows, maxRows: item.maxRows } : undefined) : item.autosize" />
              <!-- 开关 -->
              <NSwitch v-else-if="FormItemType.SWITCH === item.type" v-model:value="formData[item.id]" :checked-value="item.checkedValue" :unchecked-value="item.uncheckedValue" />
              <!-- 单选 -->
              <NRadioGroup v-else-if="FormItemType.RADIO === item.type" v-model:value="formData[item.id]" :name="item.id">
                <NSpace>
                  <NRadio v-for="(dataItem, key) in item.data" :key="key" :value="dataItem.value">
                    {{ dataItem.label }}
                  </NRadio>
                </NSpace>
              </NRadioGroup>
              <!-- 多选 -->
              <NCheckboxGroup v-else-if="FormItemType.CHECKBOX === item.type" v-model:value="formData[item.id]">
                <NSpace item-style="display: flex;">
                  <NCheckbox v-for="(dataItem, key) in item.data" :key="key" :label="dataItem.label" :value="dataItem.value" />
                </NSpace>
              </NCheckboxGroup>
              <!-- 单行文本 -->
              <NInput v-else v-model:value="formData[item.id]" />
            </NFormItemRow>
            <NSpace justify="center">
              <NButton type="primary" @click="submitForm">
                提交
              </NButton>
              <NButton v-show="formStreamContent.length > 0" @click="convertToChat">
                转为会话
              </NButton>
            </NSpace>
            <NFormItemRow label="结果：">
              <NInput
                :value="formStreamContent"
                type="textarea"
                readonly
                show-count
                class="h-full"
                :autosize="{
                  minRows: 4,
                  maxRows: 6,
                }"
                placeholder=""
              />
            </NFormItemRow>
          </NForm>
        </NSpin>
      </NTabPane>
      <NTabPane name="prompt" tab="信息">
        <NForm>
          <NFormItemRow label="提示语">
            <NInput
              :value="showPromptData?.prompt"
              type="textarea"
              readonly
              show-count
              class="h-full"
              :autosize="{
                minRows: 4,
                maxRows: 6,
              }"
              placeholder=""
            />
          </NFormItemRow>
          <NFormItemRow label="首条消息内容">
            <NInput
              :value="showPromptData?.firstMessageContent"
              type="textarea"
              readonly
              show-count
              class="h-full"
              :autosize="{
                minRows: 4,
                maxRows: 6,
              }"
              placeholder=""
            />
          </NFormItemRow>
        </NForm>
      </NTabPane>
      <NTabPane name="setting" tab="设置">
        <SettingAdvanced :setting="setting" :models="models" readonly />
      </NTabPane>
    </NTabs>
    <div v-show="'form' !== activedPromptTab" class="text-center">
      <NButton type="primary" @click="createSession">
        创建会话
      </NButton>
    </div>
  </NModal>
</template>

<style lang="less">
.prompt-list-card, .prompt-list-card > .n-card-header .n-card-header__main {
  color: inherit !important;
  transition: none !important;
}
</style>
