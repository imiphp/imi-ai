<script setup lang='tsx'>
import { NButton, NDataTable, NDatePicker, NForm, NFormItem, NIcon, NPageHeader, NSelect, NSpace } from 'naive-ui'
import type { DataTableColumns } from 'naive-ui'
import { computed, h, onMounted, reactive, ref } from 'vue'
import { SearchSharp } from '@vicons/ionicons5'
import { ENUM_ALL, cardDetails, enumValues } from '@/api'

interface Props {
  cardId: string
  visible: boolean
}

interface Emit {
  (e: 'update:visible', visible: boolean): void
}

const props = defineProps<Props>()

const emit = defineEmits<Emit>()

const show = computed({
  get: () => props.visible,
  set: (visible: boolean) => emit('update:visible', visible),
})

const createColumns = (): DataTableColumns<any> => {
  return [
    {
      title: 'ID',
      key: 'recordId',
    },
    {
      title: '业务类型',
      key: 'businessTypeText',
    },
    {
      title: '操作类型',
      key: 'operationTypeText',
    },
    {
      title: '变动金额',
      key: 'changeAmount',
      render(row) {
        return h('span', { class: row.changeAmount < 0 ? 'text-[#18a058]' : 'text-[#d03050]' }, row.changeAmount < 0 ? `${row.changeAmount}` : `+${row.changeAmount}`)
      },
    },
    {
      title: '时间',
      key: 'time',
      render(row) {
        return new Date(row.time * 1000).toLocaleString()
      },
    },
  ]
}

const businessTypes = ref(ENUM_ALL)
const operationTypes = ref(ENUM_ALL)

const conditions = ref({
  operationType: 0,
  businessType: 0,
  timeRange: ref<[number, number] | null>(null),
})
const columns = createColumns()
const tableLoading = ref(false)
const data = ref<Card.Card[]>([])
const pagination = reactive({
  page: 1,
  pageSize: 15,
  pageCount: 1,
  itemCount: 0,
  onChange: (page: number) => {
    pagination.page = page
    loadList(false)
  },
  onUpdatePageSize: (pageSize: number) => {
    pagination.pageSize = pageSize
    pagination.page = 1
  },
})

async function loadEnums() {
  const response = await enumValues(['BusinessType', 'OperationType'])
  const data = response.data
  businessTypes.value = ENUM_ALL.concat(data.BusinessType ?? [])
  operationTypes.value = ENUM_ALL.concat(data.OperationType ?? [])
}

async function loadList(resetPage = true) {
  tableLoading.value = true
  try {
    if (resetPage)
      pagination.page = 1

    const response = await cardDetails(props.cardId, conditions.value.operationType, conditions.value.businessType, conditions.value.timeRange ? parseInt((conditions.value.timeRange[0] / 1000).toString()) : 0, conditions.value.timeRange ? parseInt((conditions.value.timeRange[1] / 1000 + 86399).toString()) : 0, pagination.page, pagination.pageSize)
    data.value = response.list
    pagination.pageCount = response.pageCount
    pagination.itemCount = response.total
  }
  finally {
    tableLoading.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadList(), loadEnums()])
})
</script>

<template>
  <template v-if="show">
    <NPageHeader class="mb-4" @back="show = false">
      <template #title>
        卡号 {{ cardId }}
      </template>
    </NPageHeader>

    <NForm label-placement="left" :show-feedback="false">
      <NSpace class="flex flex-row flex-wrap">
        <NFormItem label="业务类型">
          <NSelect v-model:value="conditions.businessType" class="!w-[140px]" :options="businessTypes" label-field="text" value-field="value" />
        </NFormItem>
        <NFormItem label="操作类型">
          <NSelect v-model:value="conditions.operationType" class="!w-[120px]" :options="operationTypes" label-field="text" value-field="value" />
        </NFormItem>
        <NFormItem label="时间">
          <NDatePicker v-model:value="conditions.timeRange" class="pr-2 flex-1" type="daterange" clearable />
        </NFormItem>
        <NFormItem>
          <NButton type="primary" @click="loadList()">
            <NIcon :component="SearchSharp" size="20" />
            查询
          </NButton>
        </NFormItem>
      </NSpace>
    </NForm>

    <NDataTable
      class="mt-4"
      :columns="columns"
      :data="data"
      :bordered="false"
      :loading="tableLoading"
      scroll-x="max-content"
      remote
      :pagination="pagination"
    />
  </template>
</template>
