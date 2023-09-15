import type { Ref } from 'vue';
import { computed, watch } from 'vue';
import type { FormInst } from 'naive-ui';
import { saveConfig } from '~/src/service';
import { useBasicLayout } from '~/src/composables';

export interface ConfigComponentProps {
  value: any;
  loading: boolean;
}

export interface ConfigComponentEmit {
  (e: 'update:value', value: any): void;
  (e: 'update:loading', value: boolean): void;
}

export const defineConfigComponent = (
  configName: string,
  props: ConfigComponentProps,
  emit: ConfigComponentEmit,
  formData: Ref<any>,
  form: Ref<FormInst | undefined>
) => {
  const { isMobile } = useBasicLayout();

  const formLabelWidth = computed(() => {
    return isMobile.value ? 'auto' : '16em';
  });
  const formLabelPlacement = computed(() => {
    return isMobile.value ? 'top' : 'left';
  });

  const value = computed({
    get: () => props.value,
    set: (_value: any) => emit('update:value', _value)
  });
  const loading = computed({
    get: () => props.loading,
    set: (_value: boolean) => emit('update:loading', _value)
  });

  watch(
    () => props.value,
    newValue => {
      formData.value = {
        ...formData.value,
        ...(newValue[configName]?.config ?? {})
      };
    },
    {
      immediate: true
    }
  );

  const handleSave = async () => {
    try {
      loading.value = true;
      await form.value?.validate();
      const config = {
        [configName]: formData.value
      };
      const { data } = await saveConfig(config);
      if (data?.code === 0) {
        window.$message?.info('保存成功');
        value.value[configName].config = formData.value;
      }
    } finally {
      loading.value = false;
    }
  };

  return {
    value,
    loading,
    formData,
    handleSave,
    formLabelPlacement,
    formLabelWidth
  };
};

export const modelSelectOptions = (models: Config.Model[]) => {
  return models.map(model => ({
    label: model.title.length === 0 ? model.model : model.title,
    value: model.model
  }));
};
