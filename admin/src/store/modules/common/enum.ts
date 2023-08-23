import { ENUM_ALL, enumValues } from '~/src/service/api/config';

export async function useEnums(names: string[], withAll = false) {
  const { data } = await enumValues(names);
  const result: any = (data as any).data;
  if (withAll) {
    for (const key of Object.keys(result)) {
      result[key].unshift(ENUM_ALL);
    }
  }
  return result;
}
