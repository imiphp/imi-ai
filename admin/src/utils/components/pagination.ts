import { reactive } from 'vue';
import { useBasicLayout } from '~/src/composables';

export function defaultPaginationProps(loadData: CallableFunction, simple?: boolean) {
  const { isMobile } = useBasicLayout();
  const pagination = reactive({
    page: 1,
    pageSize: 10,
    showSizePicker: true,
    pageSizes: [10, 15, 20, 25, 30],
    pageCount: 1,
    itemCount: 0 as number | undefined,
    onChange: (page: number) => {
      pagination.page = page;
      loadData();
    },
    onUpdatePageSize: (pageSize: number) => {
      pagination.pageSize = pageSize;
      pagination.page = 1;
      loadData();
    },
    simple: simple ?? isMobile,
    prefix: () => {
      return undefined === pagination.itemCount ? '' : `共 ${pagination.itemCount} 条数据`;
    }
  });
  return pagination;
}
