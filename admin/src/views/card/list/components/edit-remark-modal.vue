<template>
  <n-modal v-model:show="modalVisible" preset="card" title="编辑备注" class="w-500px max-w-full">
    <template v-if="showResult">
      <n-input type="textarea" :value="result" rows="10"></n-input>
    </template>
    <template v-else>
      <n-form ref="formRef" label-placement="left" :label-width="80" :model="formModel">
        <n-grid cols="1" :x-gap="18" item-responsive responsive="screen">
          <n-form-item-grid-item label="备注">
            <n-input v-model:value="formModel.remark" type="textarea" rows="3" />
          </n-form-item-grid-item>
        </n-grid>
      </n-form>
    </template>
    <n-space class="w-full pt-16px" :size="24" justify="end">
      <n-button class="w-72px" @click="closeModal">取消</n-button>
      <n-button class="w-72px" type="primary" @click="handleSubmit">确定</n-button>
    </n-space>
  </n-modal>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import type { FormInst } from 'naive-ui';
import { updateCard } from '~/src/service';

export interface Props {
  /** 弹窗可见性 */
  visible: boolean;
  cardId: number;
  remark: string;
}

const props = defineProps<Props>();

interface Emits {
  (e: 'update:visible', visible: boolean): void;
}

const emit = defineEmits<Emits>();

const formRef = ref<HTMLElement & FormInst>();

const modalVisible = computed({
  get() {
    return props.visible;
  },
  set(visible) {
    emit('update:visible', visible);
  }
});
const closeModal = () => {
  modalVisible.value = false;
};

const formModelDefault = computed(() => {
  return {
    remark: props.remark
  };
});

const formModel = ref(formModelDefault.value);

const showResult = ref(false);
const result = ref('');

async function handleSubmit() {
  if (showResult.value) {
    closeModal();
    return;
  }
  const { data } = await updateCard(props.cardId, {
    remark: formModel.value.remark
  });
  if (data?.code === 0) {
    window.$message?.info('更新成功');
    closeModal();
  }
}

watch(
  () => props.visible,
  newValue => {
    if (newValue) {
      showResult.value = false;
      formModel.value = formModelDefault.value;
    }
  }
);
</script>
