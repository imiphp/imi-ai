import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { decodeSecureField, get, post } from '@/utils/request'

export * from './auth'
export * from './embedding'
export * from './vcode'
export * from './card'
export * from './config'

export function fetchChatAPI(
  prompt: string,
  options?: { conversationId?: string; parentMessageId?: string },
  signal?: GenericAbortSignal,
) {
  return post({
    url: '/chat',
    data: { prompt, options },
    signal,
  })
}

export function fetchChatAPIProcess(
  id: string,
  params: {
    signal?: GenericAbortSignal
    onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void },
) {
  const data = {
    id,
  }

  return post({
    url: '/chat/openai/stream',
    data,
    signal: params.signal,
    onDownloadProgress: params.onDownloadProgress,
  })
}

export async function getSession(
  id: string,
) {
  const response = await get({
    url: '/chat/openai/get',
    data: {
      id,
    },
  })

  decodeChatSessionSecureFields(response.data)
  for (const message of response.messages)
    decodeChatMessageSecureFields(message)

  return response
}

export async function sessionList(
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/chat/openai/list',
    data: {
      page,
      limit,
    },
  })

  if (response.list) {
    for (const item of response.list)
      decodeChatSessionSecureFields(item)
  }

  return response
}

export async function sendMessage(
  id: string,
  message: string,
) {
  const response = await post({
    url: '/chat/openai/sendMessage',
    data: {
      id,
      message,
    },
  })

  decodeChatSessionSecureFields(response.data)

  return response
}

export function editSession(
  id: string,
  title: string,
) {
  return post({
    url: '/chat/openai/edit',
    data: {
      id,
      title,
    },
  })
}

export function deleteSession(
  id: string,
) {
  return post({
    url: '/chat/openai/delete',
    data: {
      id,
    },
  })
}

function decodeChatSessionSecureFields(data: any) {
  data.title = decodeSecureField(data.title)
}

function decodeChatMessageSecureFields(data: any) {
  data.message = decodeSecureField(data.message)
}
