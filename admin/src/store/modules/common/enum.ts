import { adminEnumValues } from '~/src/service/api/config';

export const ENUM_ALL = { text: '全部', value: 0 };

export async function useAdminEnums(names: string[], withAll = false, allItem: any = ENUM_ALL) {
  const { data } = await adminEnumValues(names);
  const result: any = (data as any).data;
  if (withAll) {
    for (const key of Object.keys(result)) {
      parseEnumWithAll(result[key], allItem);
    }
  }
  return result;
}

export function parseEnumWithAll(enums: Array<any>, allItem: any = ENUM_ALL) {
  const result = enums.map(item => item);
  result.unshift(allItem);
  return result;
}
