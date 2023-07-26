import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { decodeSecureField, get, post } from '@/utils/request'

export async function projectList(
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/embedding/openai/projectList',
    data: {
      page,
      limit,
    },
  })

  for (const project of response.list)
    decodeEmbeddingProjectSecureFields(project)

  return response
}

export function deleteProject(
  id: string,
) {
  return post({
    url: '/embedding/openai/deleteProject',
    data: {
      id,
    },
  })
}

export function updateProject(
  id: string,
  data: {
    name: string
  },
) {
  return post({
    url: '/embedding/openai/updateProject',
    data: {
      id,
      ...data,
    },
  })
}

export async function getProject(
  id: string,
) {
  const response = await get({
    url: '/embedding/openai/getProject',
    data: {
      id,
    },
  })

  decodeEmbeddingProjectSecureFields(response.data)

  return response
}

export function assocFileList(
  projectId: string,
) {
  return get({
    url: '/embedding/openai/assocFileList',
    data: {
      projectId,
    },
  })
}

export async function sectionList(
  projectId: string,
  fileId: string,
) {
  const response = await get({
    url: '/embedding/openai/sectionList',
    data: {
      projectId,
      fileId,
    },
  })

  for (const section of response.list)
    decodeEmbeddingSectionSecureFields(section)

  return response
}

export async function chatList(
  id: string,
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/embedding/openai/chatList',
    data: {
      id,
      page,
      limit,
    },
  })

  for (const chat of response.list)
    decodeEmbeddingQASecureFields(chat)

  return response
}

export async function sendEmbeddingMessage(
  projectId: string,
  question: string,
  config?: any,
) {
  const response = await post({
    url: '/embedding/openai/sendMessage',
    data: {
      projectId,
      question,
      config,
    },
  })

  decodeEmbeddingQASecureFields(response.data)

  return response
}

export function fetchEmbeddingChatAPIProcess(
  id: string,
  params: {
    signal?: GenericAbortSignal
    onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void },
) {
  const data = {
    id,
  }

  return post({
    url: '/embedding/openai/stream',
    data,
    signal: params.signal,
    onDownloadProgress: params.onDownloadProgress,
  })
}

export async function embeddingFileTypes(
) {
  return await get({
    url: '/embedding/config/fileTypes',
  })
}

function decodeEmbeddingProjectSecureFields(data: any) {
  data.name = decodeSecureField(data.name)
}

function decodeEmbeddingSectionSecureFields(data: any) {
  data.content = decodeSecureField(data.content)
}

function decodeEmbeddingQASecureFields(data: any) {
  data.question = decodeSecureField(data.question)
  data.answer = decodeSecureField(data.answer)
  data.title = decodeSecureField(data.title)
}
