import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { get, post } from '@/utils/request'

export * from './auth'
export * from './embedding'
export * from './vcode'

export function fetchChatAPI<T = any>(
  prompt: string,
  options?: { conversationId?: string; parentMessageId?: string },
  signal?: GenericAbortSignal,
) {
  return post<T>({
    url: '/chat',
    data: { prompt, options },
    signal,
  })
}

export function fetchChatAPIProcess<T = any>(
  id: string,
  params: {
    signal?: GenericAbortSignal
    onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void },
) {
  const data = {
    id,
  }

  return post<T>({
    url: '/chat/openai/stream',
    data,
    signal: params.signal,
    onDownloadProgress: params.onDownloadProgress,
  })
}

export function getSession<T = any>(
  id: string,
) {
  return get<T>({
    url: '/chat/openai/get',
    data: {
      id,
    },
  })
}

export function sessionList<T = any>(
  page = 1,
  limit = 15,
) {
  return get<T>({
    url: '/chat/openai/list',
    data: {
      page,
      limit,
    },
  })
}

export function sendMessage<T = any>(
  id: string,
  message: string,
) {
  return post<T>({
    url: '/chat/openai/sendMessage',
    data: {
      id,
      message,
    },
  })
}

export function editSession<T = any>(
  id: string,
  title: string,
) {
  return post<T>({
    url: '/chat/openai/edit',
    data: {
      id,
      title,
    },
  })
}

export function deleteSession<T = any>(
  id: string,
) {
  return post<T>({
    url: '/chat/openai/delete',
    data: {
      id,
    },
  })
}
