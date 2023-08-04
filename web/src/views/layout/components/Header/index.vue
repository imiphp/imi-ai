<script lang="ts" setup>
import { computed, nextTick } from 'vue'
import { ListSharp, MenuSharp } from '@vicons/ionicons5'
import { NIcon } from 'naive-ui'
import { useAppStore, useRuntimeStore } from '@/store'

const appStore = useAppStore()

const collapsed = computed(() => appStore.siderCollapsed)
const runtimeStore = useRuntimeStore()

function handleUpdateCollapsed() {
  appStore.setSiderCollapsed(!collapsed.value)
}

function onScrollToTop() {
  const scrollRef = document.querySelector('#scrollRef')
  if (scrollRef)
    nextTick(() => scrollRef.scrollTop = 0)
}
</script>

<template>
  <header
    class="sticky top-0 left-0 right-0 z-30 border-b dark:border-neutral-800 bg-white/80 dark:bg-black/20 backdrop-blur"
  >
    <div class="relative flex items-center justify-between min-w-0 overflow-hidden h-14">
      <div class="flex items-center">
        <button
          class="flex items-center justify-center w-11 h-11"
          @click="handleUpdateCollapsed"
        >
          <NIcon size="30" :component="collapsed ? MenuSharp : ListSharp" />
        </button>
      </div>
      <h1
        class="flex-1 px-4 pr-6 overflow-hidden cursor-pointer select-none text-ellipsis whitespace-nowrap"
        @dblclick="onScrollToTop"
      >
        {{ runtimeStore?.headerTitle ?? '' }}
      </h1>
      <div class="flex items-center space-x-2">
        <slot />
      </div>
    </div>
  </header>
</template>
