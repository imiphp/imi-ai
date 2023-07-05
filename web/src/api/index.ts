import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { get, post } from '@/utils/request'

export * from './auth'
export * from './embedding'
export * from './vcode'

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

export function getSession(
  id: string,
) {
  return get({
    url: '/chat/openai/get',
    data: {
      id,
    },
  })
}

export function sessionList(
  page = 1,
  limit = 15,
) {
  return get({
    url: '/chat/openai/list',
    data: {
      page,
      limit,
    },
  })
}

export function sendMessage(
  id: string,
  message: string,
) {
  return post({
    url: '/chat/openai/sendMessage',
    data: {
      id,
      message,
    },
  })
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
