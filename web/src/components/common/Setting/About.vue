<script setup lang='tsx'>
import { ref } from 'vue'
import { NSpin } from 'naive-ui'
import pkg from '@/../package.json'

const loading = ref(false)
</script>

<template>
  <NSpin :show="loading">
    <div class="p-4 space-y-4">
      <h2 class="text-xl font-bold">
        Version - {{ pkg.version }}
      </h2>
      <div class="p-2 space-y-2 rounded-md bg-neutral-100 dark:bg-neutral-700">
        <p>
          此项目前端部分基于
          <a
            class="text-blue-600 dark:text-blue-500"
            href="https://github.com/Chanzhaoyu/chatgpt-web"
            target="_blank"
          >
            Chanzhaoyu/chatgpt-web
          </a> 开发，且本项目开源协议与原项目都是 MIT 协议，在遵循开源协议的情况下，你可以进行二开和商业应用！
        </p>
        <!-- <p>
          如果你觉得此项目对你有帮助，请在 <a
            class="text-blue-600 dark:text-blue-500"
            href="https://github.com/imiphp/imi-ai"
            target="_blank"
          >
            Github
          </a> 帮我点个 Star 或者给予一点赞助，谢谢！
        </p> -->
      </div>
    </div>
  </NSpin>
</template>
