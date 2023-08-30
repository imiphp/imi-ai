<script setup lang='tsx'>
import { NButton, NCard, NGi, NGrid, NLayout, NLayoutContent, NLayoutFooter, NSpace, useMessage } from 'naive-ui'
import { useRouter } from 'vue-router'

const message = useMessage()
const router = useRouter()

const moduleCards = [
  {
    title: 'AI聊天',
    description: 'AI 聊天是一款功能强大，智能而有趣的聊天机器人，能够陪你聊天解闷、解答问题、替你编程写代码，并支持多语言交流和翻译。',
  },
  {
    title: '模型训练',
    description: '支持上传多文件或单文件进行模型训练，可用于回答问题和客服等场景，支持多种文件解析，提供更灵活、高效的对话功能。',
  },
  {
    title: '模型训练对话',
    description: '上传的模型数据训练完成后，可以进行 AI 智能对话，AI 会帮你解读文件中的数据、总结归纳和分析文件内容，给你满意的答案。',
  },
]
</script>

<template>
  <NLayout position="absolute">
    <NLayoutContent position="absolute" class="mb-8">
      <div class="wrap relative">
        <h1 class="text-center text-4xl mt-[56px]">
          imi AI
        </h1>
        <h2 class="text-center text-2xl py-[16px]">
          你的专属 AI 人工智能助理！
        </h2>
        <NSpace justify="center" class="py-[16px]">
          <NButton type="primary" size="large" @click="router.push({ name: 'Chat' })">
            开始聊天
          </NButton>
          <NButton type="primary" size="large" @click="router.push({ name: 'Embedding' })">
            开始模型训练
          </NButton>
          <NButton size="large" disabled @click="message.info('敬请期待！')">
            源码下载
          </NButton>
        </NSpace>
        <NGrid class="mt-[32px]" x-gap="12" y-gap="16" cols="1 640:2 1024:3" item-responsive>
          <NGi v-for="(item, index) of moduleCards" :key="index">
            <NCard :title="item.title" header-style="text-align: center" content-style="height: 4em">
              <p v-text="item.description" />
            </NCard>
          </NGi>
        </NGrid>
      </div>
    </NLayoutContent>
    <NLayoutFooter
      bordered
      position="absolute"
      style="background: #fff;"
    >
      <div class="wrap relative text-center">
        <p>Copyright &copy; 2023 imi AI</p>
      </div>
    </NLayoutFooter>
  </NLayout>
</template>

<style lang="less" scoped>
h2 {
  color: #18a058;
}
</style>
