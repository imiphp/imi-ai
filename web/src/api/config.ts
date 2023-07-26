import { get } from '@/utils/request'

export async function config(
) {
  return await get({
    url: '/config/public',
  })
}
