<script setup lang='tsx'>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { NButton, NCard, NCountdown, NGi, NGrid, NIcon, NModal, NQrCode, NRadioButton, NRadioGroup, NSpace, NSpin, useMessage } from 'naive-ui'
import { useRouter } from 'vue-router'
import { config, pay, saleCardList } from '@/api'
import { convertCentToYuan, formatNumberToChinese, timespanHuman } from '@/utils/functions'
import icoAlipay from '@/assets/pay-alipay.png'
import icoWechat from '@/assets/pay-wechat.png'
import { type PayResultMonitor, waitPayResult } from '@/utils/payment'

enum TertiaryPaymentChannel {
  /**
   * 扫码支付
   */
  Native = 1,
  /**
   * JSAPI支付.
   */
  JSApi = 2,
  /**
   * 小程序支付.
   */
  MiniProgram = 3,
  /**
   * APP支付.
   */
  App = 4,
  /**
   * 简易支付.
   *
   * 一级渠道提供的收银台支付.
   */
  Easy = 5,
}

const buyCardText = import.meta.env.VITE_BUY_CARD_TEXT
const PAYMENT_METHODS = [
  {
    value: 1,
    label: '支付宝',
    icon: icoAlipay,
    color: '#00AAEE',
  },
  {
    value: 2,
    label: '微信支付',
    icon: icoWechat,
    color: '#21BD0A',
  },
]

const message = useMessage()
const router = useRouter()

const loading = ref(false)
const saleCards = ref<any[]>([])
const paymentConfig = ref<any>(null)
const showPayModal = ref(false)
const selectedPaymethodValue = ref<any>(null)
const selectedPaymethod = computed(() => {
  return PAYMENT_METHODS.find(item => item.value === selectedPaymethodValue.value)
})
/**
 * 获取三级支付渠道
 */
function getTertiaryPaymentChannels(): number[] {
  // 这里写死扫码或简易支付
  // 如果开发其它端，就需要根据环境判断返回相应的支付渠道
  return [TertiaryPaymentChannel.Native, TertiaryPaymentChannel.Easy]
}
const selectedPaymethodChannels = computed(() => {
  const tertiaryPaymentChannels = getTertiaryPaymentChannels()
  for (const tertiaryPaymentChannel of tertiaryPaymentChannels) {
    const result = paymentConfig.value?.secondaryPaymentChannels
      ?.find((item: any) => item.secondaryPaymentChannel === selectedPaymethodValue.value)
      ?.tertiaryPaymentChannels
      ?.find((item: any) => item.tertiaryPaymentChannel === tertiaryPaymentChannel)
      ?.paymentChannelName
    if (result) {
      return {
        channelName: result,
        secondaryChannel: selectedPaymethodValue.value,
        tertiaryChannel: tertiaryPaymentChannel,
      }
    }
  }
  return null
})
const buyItem = ref<any>(null)
const firstClickPay = ref(true)
const qrCodeValue = ref('')
const payUrl = ref('')
const payLoading = ref(false)
let payResultMonitor: PayResultMonitor | undefined

async function loadSaleCards() {
  const response = await saleCardList()
  const now = parseInt(((new Date()).getTime() / 1000).toString())
  response.list.forEach((item: any) => {
    if (item.saleBeginTime > 0)
      item.countDownDuration = (item.saleBeginTime - now) * 1000
    else if (item.saleEndTime > 0 && item.saleEndTime > now)
      item.countDownDuration = (item.saleEndTime - now) * 1000
    else
      item.countDownDuration = 0
  })
  saleCards.value = response.list
}

async function loadConfig() {
  const response = await config()
  paymentConfig.value = response.data['config:payment'].config ?? null
}

function calcTokenPerYuan(item: any) {
  return formatNumberToChinese(parseInt((item.amount / (item.saleActualPrice / 100)).toString()))
}

function getCardButtonText(item: any) {
  const now = parseInt(((new Date()).getTime() / 1000).toString())
  if (item.saleBeginTime > 0 && item.saleBeginTime > now)
    return '未开始'
  if (item.saleEndTime > 0 && item.saleEndTime < now)
    return '已结束'
  if (item.saleLimitQuantity > 0 && item.activationCount >= item.saleLimitQuantity)
    return '购买超限'
  return '立即购买'
}

function isEnableCardButton(item: any) {
  const now = parseInt(((new Date()).getTime() / 1000).toString())
  return (item.saleBeginTime === 0 || item.saleBeginTime <= now)
        && (item.saleEndTime === 0 || item.saleEndTime >= now)
        && (item.saleLimitQuantity === 0 || item.activationCount < item.saleLimitQuantity)
}

function onChangePaymentMethod() {
  firstClickPay.value = true
  qrCodeValue.value = ''
  payUrl.value = ''
}

function onClickBuy(item: any) {
  buyItem.value = item
  selectedPaymethodValue.value = null
  firstClickPay.value = true
  qrCodeValue.value = ''
  payUrl.value = ''
  showPayModal.value = true
}

async function onClickPay() {
  payLoading.value = true
  qrCodeValue.value = ''
  payUrl.value = ''
  try {
    const selectedPaymethodChannelsValue = selectedPaymethodChannels.value
    if (!selectedPaymethodChannelsValue) {
      message.error('没有可用的支付渠道')
      return
    }
    const response = await pay(selectedPaymethodChannelsValue.channelName, selectedPaymethodChannelsValue.secondaryChannel, selectedPaymethodChannelsValue.tertiaryChannel, buyItem.value.id)
    switch (selectedPaymethodChannelsValue.channelName) {
      // 改成你自己的支付渠道名称
      case 'xxx':
        // 二维码赋值
        // qrCodeValue.value = response.data.data.jumpUrl

        // 点击按钮跳转支付赋值
        // payUrl.value = response.data.data.jumpUrl
        break
      default:
        message.error('暂不支持该支付方式')
        return
    }
    firstClickPay.value = false
    if (payResultMonitor)
      payResultMonitor.stop()
    payResultMonitor = waitPayResult(response.data.tradeNo, (data: any) => {
      if (data.success) {
        message.success('支付成功！')
        router.push({ name: 'Card' }).then(() => {
          router.push({ name: 'Card', params: { tab: 'list' } })
        })
      }
      else { message.error(`支付失败：${data.message}`) }
    })
  }
  finally {
    payLoading.value = false
  }
}

function jumpToPayUrl() {
  location.href = payUrl.value
}

onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([loadSaleCards(), loadConfig()])
  }
  finally {
    loading.value = false
  }
})

onUnmounted(() => {
  if (payResultMonitor)
    payResultMonitor.stop()
})
</script>

<template>
  <NSpin :show="loading">
    <NGrid
      class="mt-2"
      x-gap="12"
      y-gap="16"
      :cols="3"
      item-responsive
      responsive="screen"
    >
      <NGi v-for="(item, index) of saleCards" :key="index" span="4 m:2 l:1">
        <NCard>
          <template #header>
            <p class="text-2xl">
              {{ item.name }}
            </p>
          </template>
          <template #header-extra>
            <p class="text-xl">
              {{ formatNumberToChinese(item.amount) }}
              <span class="text-gray-500 text-lg">Tokens</span>
            </p>
          </template>
          <p class="text-lg">
            约合1元 =
            <span class="font-bold">{{ calcTokenPerYuan(item) }}</span> Tokens
          </p>
          <p class="text-lg text-gray-500 mt-4">
            <template v-if="item.saleActualPrice === item.salePrice">
              <span>售价：￥{{ convertCentToYuan(item.salePrice) }}</span>
            </template>
            <template v-else>
              <span>优惠价：￥{{ convertCentToYuan(item.saleActualPrice) }}</span>
              <span class="line-through ml-2">￥{{ convertCentToYuan(item.salePrice) }}</span>
            </template>
          </p>
          <NSpace class="mt-2 center" vertical>
            <NButton
              strong
              type="error"
              size="large"
              color="#ff5678"
              text-color="#FFF"
              :disabled="!isEnableCardButton(item)"
              block
              @click="onClickBuy(item)"
            >
              {{ getCardButtonText(item) }}
              <NCountdown
                v-if="item.countDownDuration > 0"
                :duration="item.countDownDuration"
                @finish="item.countDownDuration = 0"
              />
              <span v-else-if="item.saleLimitQuantity > 0" class="ml-2">{{ item.activationCount }}/{{ item.saleLimitQuantity }}</span>
            </NButton>
            <NSpace>
              <span v-if="item.salePaying" class="text-gray-500">支持所有功能</span>
              <span v-else class="text-gray-500">仅支持部分功能</span>
              <span v-if="item.expireSeconds > 0" class="text-gray-500">有效期：{{ timespanHuman(item.expireSeconds) }}</span>
              <span v-else class="text-gray-500">永不过期</span>
            </NSpace>
          </NSpace>
        </NCard>
      </NGi>
    </NGrid>
    <pre class="font-sans mt-2 leading-6 whitespace-pre-wrap">{{
      buyCardText
    }}</pre>
  </NSpin>
  <NModal
    v-model:show="showPayModal"
    :close-on-esc="false"
    :mask-closable="false"
    preset="dialog"
    title="选择支付方式"
    style="width: 720px; max-width: 100%"
    :show-icon="false"
    :closable="!payLoading"
  >
    <NSpin :show="payLoading">
      <template v-if="paymentConfig?.secondaryPaymentChannels?.length > 0">
        <!-- 支付选择 -->
        <NRadioGroup
          v-model:value="selectedPaymethodValue"
          name="paymethod"
          size="large"
          class="mt-2"
          @update:value="onChangePaymentMethod"
        >
          <NRadioButton
            v-for="paymentMethod in PAYMENT_METHODS"
            :key="paymentMethod.value"
            :value="paymentMethod.value"
            class="text-[0]"
          >
            <NIcon
              size="32"
              class="align-middle mr-1"
              :style="[
                selectedPaymethodValue === paymentMethod.value
                  ? undefined
                  : 'filter: grayscale(1);',
              ]"
            >
              <img :src="paymentMethod.icon">
            </NIcon>
            <span class="text-base align-middle">{{
              paymentMethod.label
            }}</span>
          </NRadioButton>
        </NRadioGroup>
        <!-- 二维码 -->
        <div v-if="qrCodeValue" class="mt-4 text-center">
          <NQrCode
            :value="qrCodeValue"
            :size="256"
            :icon-src="selectedPaymethod?.icon"
          />
        </div>
        <!-- 支付按钮 -->
        <NSpace class="mt-4" justify="center">
          <NButton
            strong
            :type="firstClickPay ? 'success' : 'default'"
            :color="selectedPaymethod?.color"
            size="large"
            :disabled="!selectedPaymethodValue"
            @click="onClickPay"
          >
            {{ firstClickPay ? "立即支付" : "重新支付" }}
          </NButton>
          <NButton
            v-if="payUrl.length > 0"
            strong
            type="success"
            size="large"
            @click="jumpToPayUrl"
          >
            跳转支付
          </NButton>
        </NSpace>
      </template>
      <template v-else>
        <p>后台未配置支付渠道</p>
      </template>
    </NSpin>
  </NModal>
</template>
