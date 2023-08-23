import { ENUM_ALL, adminEnumValues, enumValues } from '~/src/service/api/config';

export async function useEnums(names: string[], withAll = false) {
  const { data } = await enumValues(names);
  const result: any = (data as any).data;
  if (withAll) {
    for (const key of Object.keys(result)) {
      parseEnumWithAll(result[key]);
    }
  }
  return result;
}

export async function useAdminEnums(names: string[], withAll = false) {
  const { data } = await adminEnumValues(names);
  const result: any = (data as any).data;
  if (withAll) {
    for (const key of Object.keys(result)) {
      parseEnumWithAll(result[key]);
    }
  }
  return result;
}

export function parseEnumWithAll(enums: Array<any>) {
  const result = enums.map(item => item);
  result.unshift(ENUM_ALL);
  return result;
}
