<script setup lang='ts'>
import { NCard, NTabPane, NTabs } from 'naive-ui'
import { ref, watch } from 'vue'
import { CardActivation, CardList } from './components'

const tab = ref('list')
const success = ref(false)

watch(success, (val) => {
  if (val)
    tab.value = 'list'
})
</script>

<template>
  <div class="wrap">
    <NCard :bordered="false" content-style="padding:0">
      <NTabs
        v-model:value="tab"
        class="card-tabs"
        size="large"
        animated
        pane-wrapper-style="margin: 0 -4px"
        pane-style="padding-left: 4px; padding-right: 4px; box-sizing: border-box;"
      >
        <NTabPane name="list" tab="卡包列表">
          <CardList />
        </NTabPane>
        <NTabPane name="activation" tab="激活卡">
          <CardActivation v-model:success="success" />
        </NTabPane>
      </NTabs>
    </NCard>
  </div>
</template>
