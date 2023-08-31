import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { decodeSecureField, get, post } from '@/utils/request'

export * from './auth'
export * from './embedding'
export * from './vcode'
export * from './card'
export * from './config'
export * from './prompt'
export * from './invitation'

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
    url: '/chat/stream',
    data,
    signal: params.signal,
    onDownloadProgress: params.onDownloadProgress,
  })
}

export async function getSession(
  id: string,
) {
  const response = await get({
    url: '/chat/get',
    data: {
      id,
    },
  })

  decodeChatSessionSecureFields(response.data)
  if (response.messages) {
    for (const message of response.messages)
      decodeChatMessageSecureFields(message)
  }

  return response
}

export async function sessionList(
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/chat/list',
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

export async function messageList(
  sessionId: string,
  lastMessageId = '',
  limit = 15,
) {
  const response = await get({
    url: '/chat/messageList',
    data: {
      sessionId,
      lastMessageId,
      limit,
    },
  })

  if (response.list) {
    for (const item of response.list)
      decodeChatMessageSecureFields(item)
  }

  return response
}

export async function sendMessage(
  id: string,
  message: string,
  prompt?: string,
  config?: Chat.ChatSetting,
) {
  const response = await post({
    url: '/chat/sendMessage',
    data: {
      id,
      message,
      prompt,
      config,
    },
  })

  decodeChatSessionSecureFields(response.data)
  decodeChatMessageSecureFields(response.chatMessage)

  return response
}

export function editSession(data: {
  id: string
  title?: string
  prompt?: string
  config?: Chat.ChatSetting
}) {
  return post({
    url: '/chat/edit',
    data,
  })
}

export function deleteSession(
  id: string,
) {
  return post({
    url: '/chat/delete',
    data: {
      id,
    },
  })
}

export function decodeChatSessionSecureFields(data: any) {
  data.title = decodeSecureField(data.title)
  data.prompt = decodeSecureField(data.prompt)
}

export function decodeChatMessageSecureFields(data: any) {
  data.message = decodeSecureField(data.message)
}
