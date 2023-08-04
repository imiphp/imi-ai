import type { AxiosResponse } from 'axios'
import post, { get } from '@/utils/request'
import type { Response } from '@/utils/request'

export async function cardInfo(
  failHandler?: (res: AxiosResponse<Response>) => void,
) {
  const response = await get({
    url: '/card/info',
    failHandler,
  })

  return response
}

export async function cardList(
  expired: boolean | null = null,
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/card/list',
    data: {
      expired: expired ? 1 : 0,
      page,
      limit,
    },
  })

  return response
}

export async function activation(
  data: {
    cardId: string
    vcodeToken: string
    vcode: string
  },
) {
  const response = await post({
    url: '/card/activation',
    data,
  })

  return response
}

export async function memberCardDetails(
  operationType = 0,
  businessType = 0,
  beginTime = 0,
  endTime = 0,
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/card/details',
    data: {
      operationType,
      businessType,
      beginTime,
      endTime,
      page,
      limit,
    },
  })

  return response
}
