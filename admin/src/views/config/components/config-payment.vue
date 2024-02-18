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
      <n-card title="业务支付渠道">
        <n-space vertical>
          <n-card
            v-for="secondaryPaymentChannel in formData.secondaryPaymentChannels"
            :key="secondaryPaymentChannel.secondaryPaymentChannel"
            :title="secondaryPaymentChannel.title"
          >
            <n-table>
              <n-tr
                v-for="tertiariePaymentChannel in secondaryPaymentChannel.tertiaryPaymentChannels"
                :key="tertiariePaymentChannel.tertiaryPaymentChannel"
              >
                <n-td class="w-[10em] text-right">{{ tertiariePaymentChannel.title }}</n-td>
                <n-td>
                  <n-select
                    v-model:value="tertiariePaymentChannel.paymentChannelName"
                    :options="
                      getPaymentChannelOptions(
                        secondaryPaymentChannel.secondaryPaymentChannel,
                        tertiariePaymentChannel.tertiaryPaymentChannel
                      )
                    "
                  ></n-select>
                </n-td>
              </n-tr>
            </n-table>
          </n-card>
        </n-space>
      </n-card>
      <n-card title="对接支付渠道">
        <n-tabs :default-value="formData.channels[0].name ?? undefined" pane-wrapper-style="overflow-y: auto">
          <n-tab-pane
            v-for="channel in formData.channels"
            :key="channel.name"
            :name="channel.name"
            :tab="channel.title"
          >
            <n-grid cols="1" :y-gap="18" item-responsive responsive="screen">
              <n-form-item-grid-item label="状态：" class="mb-[16px]">
                <n-switch v-model:value="channel.enable">
                  <template #checked>启用</template>
                  <template #unchecked>禁用</template>
                </n-switch>
              </n-form-item-grid-item>
            </n-grid>
            <template v-if="false"></template>
            <!-- <xxx v-if="'支付渠道名称' === channel.name" v-model="channel.config"></xxx> -->
            <p v-else>
              请实现
              <b>{{ channel.name }}</b>
              类型的配置管理
            </p>
          </n-tab-pane>
        </n-tabs>
      </n-card>
    </n-space>
    <n-space class="w-full pt-16px" :size="24" justify="center">
      <n-button class="w-72px" type="primary" @click="handleSave">保存</n-button>
    </n-space>
  </n-form>
</template>

<script setup lang="tsx">
import { ref } from 'vue';
import type { FormInst, FormRules } from 'naive-ui';
import { defineConfigComponent } from '@/store';
import type { ConfigComponentProps, ConfigComponentEmit } from '@/store';

type SubChannel = {
  secondary: number;
  secondaryTitle: string;
  tertiaries: {
    tertiary: number;
    tertiaryTitle: string;
  }[];
};

type Channel = {
  enable: boolean;
  name: string;
  title: string;
  config: Record<string, unknown>;
  subChannels: SubChannel[];
};

type FormModel = {
  channels: Channel[];
  secondaryPaymentChannels: {
    secondaryPaymentChannel: number;
    title: string;
    tertiaryPaymentChannels: {
      tertiaryPaymentChannel: number;
      title: string;
      paymentChannelName: string;
    }[];
  }[];
};

const props = defineProps<ConfigComponentProps>();
const emit = defineEmits<ConfigComponentEmit>();
const rules: FormRules = {};
const formData = ref<FormModel>({
  channels: [],
  secondaryPaymentChannels: []
});
const form = ref<FormInst>();

const { handleSave, formLabelPlacement, formLabelWidth } = defineConfigComponent(
  'config:payment',
  props,
  emit,
  formData,
  form
);

function getPaymentChannelOptions(secondaryPaymentChannel: number, tertiaryPaymentChannel: number) {
  const channels: any = [
    {
      label: '禁用',
      value: ''
    }
  ];
  formData.value.channels.forEach(channel => {
    channel.subChannels.forEach(subChannel => {
      if (
        subChannel.secondary === secondaryPaymentChannel &&
        subChannel.tertiaries.some(t => t.tertiary === tertiaryPaymentChannel)
      ) {
        channels.push({
          label: channel.title,
          value: channel.name
        });
      }
    });
  });
  return channels;
}
</script>

<style scoped></style>
