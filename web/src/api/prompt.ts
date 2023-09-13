import { decodeChatSessionSecureFields } from '.'
import post, { get } from '@/utils/request'

export async function promptCategoryList(
) {
  return await get({
    url: '/chat/promptCategory/list',
  })
}

export async function promptList(
  type: number,
  categoryIds: string | string[],
  search = '',
  page = 1,
  limit = 15,
) {
  return await get({
    url: '/chat/prompt/list',
    data: {
      type,
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

export async function submitPromptForm(
  id: string,
  data: any,
) {
  const response = await post({
    url: '/chat/prompt/submitForm',
    data: {
      id,
      data,
    },
  })

  decodeChatSessionSecureFields(response.data)

  return response
}

export async function convertPromptFormToChat(
  sessionId: string,
) {
  return await post({
    url: '/chat/prompt/convertFormToChat',
    data: {
      sessionId,
    },
  })
}
