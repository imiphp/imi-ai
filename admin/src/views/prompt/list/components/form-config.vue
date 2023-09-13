<template>
  <n-scrollbar y-scrollable class="max-h-full">
    <n-table :single-line="false" striped class="w-max min-w-full api-manager-table">
      <thead>
        <tr class="text-center">
          <th width="150">名称</th>
          <th width="150">标题</th>
          <th width="90">类型</th>
          <th width="70">必填</th>
          <th width="400">配置</th>
          <th width="50">删除</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item, index) in valueData" :key="index">
          <td><n-input v-model:value="item.id" /></td>
          <td><n-input v-model:value="item.label" /></td>
          <td><n-select v-model:value="item.type" :options="types" /></td>
          <td class="text-center"><n-switch v-model:value="item.required" /></td>
          <td>
            <p v-if="'select' === item.type || 'radio' === item.type || 'checkbox' === item.type">
              <b>数据：（文本 = 值）</b>
              <n-dynamic-input
                v-model:value="item.dataComponent"
                preset="pair"
                key-placeholder="文本"
                value-placeholder="值"
                placeholder="请输入"
              ></n-dynamic-input>
            </p>
            <template v-if="'textarea' === item.type">
              <p>
                <b>自动换行：</b>
                <n-radio-group v-model:value="item.autosize" name="autosize">
                  <n-radio-button :value="'undefined'" label="默认"></n-radio-button>
                  <n-radio-button :value="true" label="启用"></n-radio-button>
                  <n-radio-button :value="false" label="禁用"></n-radio-button>
                </n-radio-group>
              </p>
              <p v-if="'undefined' === item.autosize">
                <b>行数：</b>
                <n-input v-model:value="item.rowsArr" pair separator="-" clearable />
              </p>
            </template>
          </td>
          <td>
            <n-popconfirm :on-positive-click="() => handleDelete(index)">
              <template #default>确认删除？</template>
              <template #trigger>
                <n-button text block type="primary">删除</n-button>
              </template>
            </n-popconfirm>
          </td>
        </tr>
      </tbody>
    </n-table>
    <n-space justify="center" class="mt-2">
      <n-button type="primary" @click="handleAdd">
        <icon-ic-round-plus class="mr-4px text-20px" />
        增加一项
      </n-button>
    </n-space>
    {{ value }}
  </n-scrollbar>
</template>

<script setup lang="tsx">
import { reactive, watch } from 'vue';

export interface FormItem {
  id: string;
  label: string;
  type: string;
  required: boolean;
  data?: {
    label: string;
    value: any;
  }[];
  autosize?: boolean | string;
  minRows?: number;
  maxRows?: number;
}

export type FormItemData = {
  dataComponent?: { key: string; value: string }[];
  rowsArr: [string, string];
} & FormItem;

export interface Props {
  value: FormItem[];
}

export interface Emit {
  (e: 'update:value', value: FormItem[]): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emit>();

const valueData = reactive<FormItemData[]>(
  (() => {
    return props.value.map(item => {
      let rowsArr;
      if (item.minRows || item.maxRows) {
        rowsArr = [(item.minRows ?? '0').toString(), (item.maxRows ?? '0').toString()];
      }
      return {
        ...item,
        dataComponent: item.data?.map(item2 => {
          return { key: item2.label, value: item2.value };
        }),
        autosize: undefined === item.autosize ? 'undefined' : item.autosize,
        rowsArr
      };
    });
  })() as unknown as FormItemData[]
);

watch(
  valueData,
  _value => {
    const result = _value.map(item => {
      const dataValue: any[] = [];
      item.dataComponent?.forEach(item2 => {
        if (item2.key.length === 0 && item2.value.length === 0) return;
        dataValue.push({
          label: item2.key,
          value: item2.value
        });
      });
      const data: any = {
        ...item,
        data: dataValue,
        autosize: item.autosize === 'undefined' ? undefined : item.autosize,
        minRows: item.rowsArr && item.rowsArr[0].length > 0 ? parseInt(item.rowsArr[0]) ?? undefined : undefined,
        maxRows: item.rowsArr && item.rowsArr[1].length > 0 ? parseInt(item.rowsArr[1]) ?? undefined : undefined
      };
      delete data.dataComponent;
      delete data.rowsArr;
      return data;
    }) as FormItem[];
    emit('update:value', result);
  },
  { deep: true }
);

const types = [
  { label: '单行文本', value: 'text' },
  { label: '多行文本', value: 'textarea' },
  { label: '下拉框', value: 'select' },
  { label: '单选框', value: 'radio' },
  { label: '多选框', value: 'checkbox' },
  { label: '开关', value: 'switch' }
];

function handleAdd() {
  valueData.push({
    id: '',
    label: '',
    type: 'text',
    required: false,
    autosize: 'undefined',
    rowsArr: ['', '']
  });
}

function handleDelete(index: number) {
  valueData.splice(index, 1);
}
</script>

<style lang="less"></style>
