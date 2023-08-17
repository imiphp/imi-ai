<script setup lang='ts'>
import { NCard, NTabPane, NTabs } from 'naive-ui'
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { CardActivation, CardList, MemberCardDetails } from './components'

const route = useRoute()
const tab = ref(route.params.tab ? route.params.tab.toString() : 'list')
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
          <CardList :expired="false" />
        </NTabPane>
        <NTabPane name="details" tab="交易明细">
          <MemberCardDetails />
        </NTabPane>
        <NTabPane name="activation" tab="卡号激活">
          <CardActivation v-model:success="success" />
        </NTabPane>
        <NTabPane name="expiredList" tab="过期卡包">
          <CardList :expired="true" />
        </NTabPane>
      </NTabs>
    </NCard>
  </div>
</template>
