import { get } from '@/utils/request'

export function vcode<T = any>() {
  return get<T>({
    url: '/vcode/get',
  })
}
