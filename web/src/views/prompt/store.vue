<script setup lang='ts'>
import { NBreadcrumb, NBreadcrumbItem, NButton, NCard, NEllipsis, NGi, NGrid, NInput, NModal, NPagination, NSpace, NSpin, NTag } from 'naive-ui'
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { promptList as getPromptList, promptCategoryList } from '@/api'

const router = useRouter()
const showLoading = ref(false)
const categoryList = ref<any[]>([])
const categoryId = ref('')
watch(categoryId, () => {
  loadPromptList()
})
const promptList = ref<any[]>([])
const showPromptData = ref<any>(null)
const page = ref(1)
const pageSize = ref(15)
const pageCount = ref(1)

async function onUpdateChange(_page: number) {
  page.value = _page
  await loadPromptList()
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
      '',
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

function createSession() {
  router.push({
    name: 'Chat',
    query: {
      promptId: showPromptData.value.recordId,
    },
  })
}

onMounted(async () => {
  try {
    showLoading.value = true
    await Promise.all([
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
        首页
      </NBreadcrumbItem>
      <NBreadcrumbItem>
        模型市场
      </NBreadcrumbItem>
    </NBreadcrumb>
    <NSpin :show="showLoading">
      <!-- 提示语分类 -->
      <NSpace class="mt-2">
        <NTag :checked="categoryId === ''" checkable @click="categoryId = ''">
          全部
        </NTag>
        <NTag v-for="(item, index) of categoryList" :key="index" :checked="categoryId === item.recordId" checkable @click="categoryId = item.recordId">
          {{ item.title }}
        </NTag>
      </NSpace>
      <!-- 提示语列表 -->
      <NGrid class="mt-2" x-gap="12" y-gap="16" cols="1 s:2 l:3" item-responsive responsive="screen">
        <NGi v-for="(item, index) of promptList" :key="index">
          <a class="block hover:!text-gray-500" href="javascript:;" @click="showPromptData = { ...item }">
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
      <NPagination v-model:page="page" class="mt-4 float-right" :page-count="pageCount" :on-update:page="onUpdateChange" />
    </NSpin>
  </div>
  <!-- 提示语弹窗 -->
  <NModal
    :show="!!showPromptData"
    preset="card"
    :title="showPromptData?.title"
    style="width: 640px; max-width: 100vw; max-height: 100vh"
    mask-closable
    @update-show="(show) => { showPromptData = show ? showPromptData : null }"
    @close="showPromptData = null"
  >
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
    />
    <div class="text-center mt-4">
      <NButton type="primary" @click="createSession()">
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
