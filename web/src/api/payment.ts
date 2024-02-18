import { get } from '@/utils/request'

export async function result(tradeNo: string) {
  return await get({
    url: '/payment/result',
    data: { tradeNo },
  })
}
