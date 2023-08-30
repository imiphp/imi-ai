<script setup lang='tsx'>
import { NAlert, NBreadcrumb, NBreadcrumbItem, NGi, NGrid, NInput, NSelect, NStatistic } from 'naive-ui'
import { computed, ref } from 'vue'
import type { TiktokenModel } from 'tiktoken'
import { encoding_for_model } from 'tiktoken'
import { getSegments } from '@/utils/segments'

const COLORS = [
  'bg-sky-200',
  'bg-amber-200',
  'bg-blue-200',
  'bg-green-200',
  'bg-orange-200',
  'bg-cyan-200',
  'bg-gray-200',
  'bg-purple-200',
  'bg-indigo-200',
  'bg-lime-200',
  'bg-rose-200',
  'bg-violet-200',
  'bg-yellow-200',
  'bg-emerald-200',
  'bg-zinc-200',
  'bg-red-200',
  'bg-fuchsia-200',
  'bg-pink-200',
  'bg-teal-200',
]

const models = (() => {
  const result = []
  for (const model of [
    'gpt-4',
    'gpt-4-32k',
    'gpt-3.5-turbo',
    'gpt-3.5-turbo-16k',
    'gpt2',
    'gpt-4-0314',
    'gpt-4-0613',
    'gpt-4-32k-0314',
    'gpt-4-32k-0613',
    'gpt-3.5-turbo-0301',
    'gpt-3.5-turbo-0613',
    'gpt-3.5-turbo-16k-0613',
    'davinci',
    'curie',
    'babbage',
    'ada',
    'text-davinci-003',
    'text-davinci-002',
    'text-davinci-001',
    'text-curie-001',
    'text-babbage-001',
    'text-ada-001',
    'code-davinci-002',
    'code-davinci-001',
    'code-cushman-002',
    'code-cushman-001',
    'davinci-codex',
    'cushman-codex',
    'text-davinci-edit-001',
    'code-davinci-edit-001',
    'text-embedding-ada-002',
    'text-similarity-davinci-001',
    'text-similarity-curie-001',
    'text-similarity-babbage-001',
    'text-similarity-ada-001',
    'text-search-davinci-doc-001',
    'text-search-curie-doc-001',
    'text-search-babbage-doc-001',
    'text-search-ada-doc-001',
    'code-search-babbage-code-001',
    'code-search-ada-code-001',
  ])
    result.push({ label: model, value: model })

  return result
})()

const inputContent = ref('PHP是世界上最好的编程语言！')
const model = ref<TiktokenModel>('gpt-4')
const enc = computed(() => encoding_for_model(model.value))
const segments = computed(() => getSegments(enc.value, inputContent.value))
const hoverSegmentIndex = ref<number | null>(null)
const hoverTokenIndex = ref<number | null>(null)

function getSegmentTextClass(index: number) {
  if ((hoverSegmentIndex.value === null && (hoverTokenIndex.value === null || hoverTokenIndex.value === index)) || hoverSegmentIndex.value === index)
    return COLORS[index % COLORS.length]
  else
    return 'bg-transparent'
}

function getTokensTextClass(index: number) {
  if (hoverTokenIndex.value === index || hoverSegmentIndex.value === index)
    return COLORS[index % COLORS.length]
}
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
        Token 分词器
      </NBreadcrumbItem>
    </NBreadcrumb>
    <!-- 提示 -->
    <NAlert title="提示" type="info">
      AI 模型会将文本分词为 Tokens，你可以使用下面的工具来了解各模型分词后的 Tokens 数量。
    </NAlert>
    <NGrid class="mt-4" x-gap="12" y-gap="16" cols="1 1024:2" item-responsive>
      <NGi>
        <!-- 选择模型 -->
        <div class="flex items-center">
          <span>
            模型：
          </span>
          <NSelect v-model:value="model" class="flex-1" :options="models" />
        </div>
        <!-- 输入框 -->
        <NInput v-model:value="inputContent" class="mt-4" type="textarea" rows="10" />
        <!-- 统计 -->
        <NGrid class="mt-4" x-gap="12" y-gap="16" cols="2 1024:4" item-responsive>
          <NGi>
            <NStatistic label="Tokens">
              {{ segments.tokensCount }}
            </NStatistic>
          </NGi>
          <NGi>
            <NStatistic label="字符数量">
              {{ inputContent.length }}
            </NStatistic>
          </NGi>
        </NGrid>
      </NGi>
      <NGi>
        <!-- 原文本 -->
        <pre class="min-h-[256px] max-w-[100vw] overflow-auto whitespace-pre-wrap break-all rounded-md border bg-slate-50 p-4 shadow-sm"><span v-for="(item, index) in segments.segments" :key="index" class="transition-all" :class="getSegmentTextClass(index)" @mouseenter="hoverSegmentIndex = index" @mouseleave="hoverSegmentIndex = null">{{ item.text }}</span></pre>
        <!-- Tokens -->
        <pre class="tokens-pre mt-4 min-h-[256px] max-w-[100vw] overflow-auto whitespace-pre-wrap break-all rounded-md border bg-slate-50 p-4 shadow-sm">[<template v-for="(item, index) in segments.segments" :key="index"><span class="transition-all" :class="getTokensTextClass(index)" @mouseenter="hoverTokenIndex = index" @mouseleave="hoverTokenIndex = null"><template v-for="token in item.tokens" :key="token.idx"><span>{{ token.id }}</span><span class="!p-0">,</span></template></span><span class="!p-0">,</span></template>]</pre>
      </NGi>
    </NGrid>
  </div>
</template>

<style lang="less" scoped>
.tokens-pre > span {
    display: inline-block;
}
.tokens-pre > span > span {
    padding: 0.5em
}
.tokens-pre > span:last-child, .tokens-pre > span > span:last-child{
    display: none
}
.tokens-pre > span > span:first-child{
    display: initial
}
</style>
