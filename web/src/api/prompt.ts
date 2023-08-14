import { get } from '@/utils/request'

export async function promptCategoryList(
) {
  return await get({
    url: '/chat/promptCategory/list',
  })
}

export async function promptList(
  categoryIds: string | string[],
  search = '',
  page = 1,
  limit = 15,
) {
  return await get({
    url: '/chat/prompt/list',
    data: {
      categoryIds,
      search,
      page,
      limit,
    },
  })
}

export async function getPrompt(
  id: string,
) {
  return await get({
    url: '/chat/prompt/get',
    data: {
      id,
    },
  })
}
