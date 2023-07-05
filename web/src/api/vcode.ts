import { get } from '@/utils/request'

export function vcode<T = any>() {
  return get({
    url: '/vcode/get',
  })
}
