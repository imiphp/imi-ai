<script setup lang='ts'>
import { computed, h, ref } from 'vue'
import { NDropdown, NIcon, useMessage } from 'naive-ui'
import { MdMore } from '@vicons/ionicons4'
import { CodeSharp, CodeSlash, CopyOutline } from '@vicons/ionicons5'
import AvatarComponent from './Avatar.vue'
import TextComponent from './Text.vue'
import { t } from '@/locales'
import { useBasicLayout } from '@/hooks/useBasicLayout'
import { copyToClip } from '@/utils/copy'

interface Props {
  dateTime?: number
  text?: string
  inversion?: boolean
  error?: boolean
  loading?: boolean
  tokens?: number
  tokenPrefix?: string
}

interface Emit {
  (ev: 'regenerate'): void
  (ev: 'delete'): void
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const { isMobile } = useBasicLayout()

const message = useMessage()

const textRef = ref<HTMLElement>()

const asRawText = ref(props.inversion)

const messageRef = ref<HTMLElement>()

const options = computed(() => {
  const common = [
    {
      label: t('chat.copy'),
      key: 'copyText',
      icon: () => h(NIcon, null, { default: () => h(CopyOutline) }),
    },
    // {
    //   label: t('common.delete'),
    //   key: 'delete',
    //   icon: iconRender({ icon: 'ri:delete-bin-line' }),
    // },
  ]

  if (!props.inversion) {
    common.unshift({
      label: asRawText.value ? t('chat.preview') : t('chat.showRawText'),
      key: 'toggleRenderType',
      icon: () => h(NIcon, null, { default: () => h(asRawText.value ? CodeSlash : CodeSharp) }),
    })
  }

  return common
})

function handleSelect(key: 'copyText' | 'delete' | 'toggleRenderType') {
  switch (key) {
    case 'copyText':
      handleCopy()
      return
    case 'toggleRenderType':
      asRawText.value = !asRawText.value
      return
    case 'delete':
      emit('delete')
  }
}

async function handleCopy() {
  try {
    await copyToClip(props.text || '')
    message.success('复制成功')
  }
  catch {
    message.error('复制失败')
  }
}
</script>

<template>
  <div
    ref="messageRef"
    class="flex w-full mb-6 overflow-hidden"
    :class="[{ 'flex-row-reverse': inversion }]"
  >
    <div
      class="flex items-center justify-center flex-shrink-0 h-8 overflow-hidden rounded-full basis-8"
      :class="[inversion ? 'ml-2' : 'mr-2']"
    >
      <AvatarComponent :image="inversion" />
    </div>
    <div class="overflow-hidden text-sm " :class="[inversion ? 'items-end' : 'items-start']">
      <p class="text-xs text-[#b4bbc4]" :class="[inversion ? 'text-right' : 'text-left']">
        {{ (new Date((dateTime ?? 0) * 1000)).toLocaleString() }}
        <template v-if="undefined !== tokens">
          | {{ tokenPrefix }}Tokens：{{ tokens }}
        </template>
      </p>
      <div
        class="flex items-end gap-1 mt-2"
        :class="[inversion ? 'flex-row-reverse' : 'flex-row']"
      >
        <TextComponent
          ref="textRef"
          :inversion="inversion"
          :error="error"
          :text="text"
          :loading="loading"
          :as-raw-text="asRawText"
        />
        <div class="flex flex-col">
          <!-- <button
                    v-if="!inversion"
                    class="mb-2 transition text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-300"
                    @click="handleRegenerate"
                  >
                    <SvgIcon icon="ri:restart-line" />
                  </button> -->
          <NDropdown
            :trigger="isMobile ? 'click' : 'hover'"
            :placement="!inversion ? 'right' : 'left'"
            :options="options"
            @select="handleSelect"
          >
            <button class="transition text-neutral-300 hover:text-neutral-800 dark:hover:text-neutral-200">
              <NIcon :component="MdMore" />
            </button>
          </NDropdown>
        </div>
      </div>
    </div>
  </div>
</template>
