import post, { get } from '@/utils/request'

export async function cardInfo(
) {
  const response = await get({
    url: '/card/info',
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
