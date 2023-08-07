import { get } from '@/utils/request'

export const ENUM_ALL = [{ text: '全部', value: 0 }]

export async function config(
) {
  return await get({
    url: '/config/public',
  })
}

export async function enumValues(
  name?: string | string[],
) {
  if (typeof name === 'object')
    name = name.join(',')
  return await get({
    url: '/enum/values',
    data: {
      name,
    },
  })
}
