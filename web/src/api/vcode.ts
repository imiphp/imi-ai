import { get } from '@/utils/request'

export function vcode() {
  return get({
    url: '/vcode/get',
  })
}
