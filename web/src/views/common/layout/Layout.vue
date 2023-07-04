<script setup lang='ts'>
import { computed } from 'vue'
import { NLayout, NLayoutContent, NLayoutHeader } from 'naive-ui'
import Navigate from '../../layout/Navigate.vue'
import { useBasicLayout } from '@/hooks/useBasicLayout'

const { isMobile } = useBasicLayout()

const getMobileClass = computed(() => {
  if (isMobile.value)
    return ['rounded-none', 'shadow-none']
  return ['border', 'rounded-md', 'shadow-md', 'dark:border-neutral-800', 'relative']
})

const getContainerClass = computed(() => {
  return [
    'h-full',
  ]
})
</script>

<template>
  <div class="h-full dark:bg-[#24272e] transition-all" :class="[isMobile ? 'p-0' : 'p-4']">
    <div class="h-full overflow-hidden" :class="getMobileClass">
      <NLayout position="absolute">
        <NLayoutHeader>
          <Navigate />
        </NLayoutHeader>
        <NLayout class="z-40 transition !h-[calc(100%-49px)]" :class="getContainerClass">
          <NLayoutContent class="h-full">
            <RouterView v-slot="{ Component, route }">
              <component :is="Component" :key="route.fullPath" />
            </RouterView>
          </NLayoutContent>
        </NLayout>
      </NLayout>
    </div>
  </div>
</template>
