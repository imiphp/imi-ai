<script setup lang='ts'>
import { NCard, NTabPane, NTabs } from 'naive-ui'
import { ProjectList, Upload } from './components'
</script>

<template>
  <div class="wrap">
    <NCard :bordered="false" content-style="padding:0">
      <NTabs
        class="card-tabs"
        default-value="projectList"
        size="large"
        animated
        pane-wrapper-style="margin: 0 -4px"
        pane-style="padding-left: 4px; padding-right: 4px; box-sizing: border-box;"
      >
        <NTabPane name="projectList" tab="我的项目">
          <ProjectList />
        </NTabPane>
        <NTabPane name="upload" tab="上传文件">
          <Upload />
        </NTabPane>
      </NTabs>
    </NCard>
  </div>
</template>
