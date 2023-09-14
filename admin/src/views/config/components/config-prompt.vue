<template>
  <n-form
    ref="form"
    :rules="rules"
    :label-placement="formLabelPlacement"
    :label-width="formLabelWidth"
    class="max-w-[1280px]"
    :show-feedback="false"
  >
    <n-space vertical>
      <n-card>
        <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="临时记录保存时长：">
            <n-input-number v-model:value="formData.tempRecordTTL" :min="0">
              <template #suffix>秒</template>
            </n-input-number>
            <span class="ml-2">{{ timespanHuman(formData.tempRecordTTL) }}</span>
          </n-form-item-grid-item>
        </n-grid>
      </n-card>
      <n-card title="采集器配置">
        <n-spin :show="loading">
          <n-scrollbar x-scrollable>
            <n-table :single-line="false" striped class="w-max min-w-full">
              <thead>
                <tr class="text-center">
                  <th width="200">标题</th>
                  <th width="440">类名</th>
                  <th width="410">来源</th>
                  <th width="70">启用</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in crawlerList" :key="index">
                  <td v-text="item.title"></td>
                  <td v-text="item.class"></td>
                  <td>
                    <n-a :href="item.url" target="_blank">{{ item.url }}</n-a>
                  </td>
                  <td class="text-center"><n-switch v-model:value="item.enable" /></td>
                </tr>
              </tbody>
            </n-table>
          </n-scrollbar>
        </n-spin>
      </n-card>
    </n-space>
    <n-space class="w-full pt-16px" :size="24" justify="center">
      <n-button class="w-72px" type="primary" @click="handleSave">保存</n-button>
    </n-space>
  </n-form>
</template>

<script setup lang="tsx">
import { onMounted, ref, watch } from 'vue';
import type { FormInst, FormRules } from 'naive-ui';
import { defineConfigComponent } from '@/store';
import type { ConfigComponentProps, ConfigComponentEmit } from '@/store';
import { timespanHuman } from '@/utils';
import { fetchPromptCrawlerList } from '~/src/service';

const props = defineProps<ConfigComponentProps>();
const emit = defineEmits<ConfigComponentEmit>();
const rules: FormRules = {};
const formData = ref({
  crawlers: [] as string[],
  tempRecordTTL: 0
});
const form = ref<FormInst>();
const loading = ref(false);

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:prompt',
  props,
  emit,
  formData,
  form
);

type CrawlerItem = Prompt.PromptCrawler & { enable: boolean };

const crawlerList = ref<CrawlerItem[]>([]);

watch(
  crawlerList,
  () => {
    formData.value.crawlers = crawlerList.value
      .filter((item: CrawlerItem) => item.enable)
      .map((item: CrawlerItem) => item.class);
  },
  { deep: true }
);

async function loadCrawlerList() {
  loading.value = true;
  try {
    const { data } = await fetchPromptCrawlerList();
    if (data) {
      crawlerList.value = data.list.map((item: Prompt.PromptCrawler) => ({
        ...item,
        enable: formData.value.crawlers.includes(item.class)
      }));
    }
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  await loadCrawlerList();
});
</script>

<style scoped></style>
