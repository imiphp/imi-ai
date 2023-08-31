import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { decodeSecureField, get, post } from '@/utils/request'
import { PublicProjectStatus } from '@/store/modules/embedding'

export async function projectList(
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/embedding/projectList',
    data: {
      page,
      limit,
    },
  })

  for (const project of response.list)
    decodeEmbeddingProjectSecureFields(project)

  return response
}

export async function publicProjectList(
  page = 1,
  limit = 15,
) {
  const response = await get({
    url: '/embedding/publicProjectList',
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
    url: '/embedding/deleteProject',
    data: {
      id,
    },
  })
}

export function updateProject(
  id: string,
  data: {
    name?: string
    public?: boolean
    publicList?: boolean
    sectionSeparator?: string
    sectionSplitLength?: number
    sectionSplitByTitle?: boolean
    chatConfig?: any
    similarity?: number
    topSections?: number
    prompt?: string
  },
) {
  return post({
    url: '/embedding/updateProject',
    data: {
      id,
      name: data.name,
      public: data.public,
      publicList: data.publicList,
      sectionSeparator: data.sectionSeparator,
      sectionSplitLength: data.sectionSplitLength,
      sectionSplitByTitle: data.sectionSplitByTitle,
      chatConfig: data.chatConfig,
      similarity: data.similarity,
      topSections: data.topSections,
      prompt: data.prompt,
    },
  })
}

export async function getProject(
  id: string,
) {
  const response = await get({
    url: '/embedding/getProject',
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
    url: '/embedding/assocFileList',
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
    url: '/embedding/sectionList',
    data: {
      projectId,
      fileId,
    },
  })

  for (const section of response.list)
    decodeEmbeddingSectionSecureFields(section)

  return response
}

export async function getSection(
  id: string,
) {
  const response = await get({
    url: '/embedding/getSection',
    data: {
      id,
    },
  })

  decodeEmbeddingSectionSecureFields(response.data)

  return response
}

export async function getFile(
  id: string,
) {
  const response = await get({
    url: '/embedding/getFile',
    data: {
      id,
    },
  })

  decodeEmbeddingFileSecureFields(response.data)

  return response
}

export async function chatList(
  id: string,
  lastMessageId = '',
  limit = 15,
) {
  const response = await get({
    url: '/embedding/chatList',
    data: {
      id,
      lastMessageId,
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
  config?: Chat.ChatSetting,
  similarity?: number,
  topSections?: number,
  prompt?: string,
) {
  const response = await post({
    url: '/embedding/sendMessage',
    data: {
      projectId,
      question,
      config,
      similarity,
      topSections,
      prompt,
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
    url: '/embedding/stream',
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

export function retryProject(
  id: string,
) {
  return post({
    url: '/embedding/retryProject',
    data: {
      id,
    },
  })
}

export function retryFile(
  id: string,
) {
  return post({
    url: '/embedding/retryFile',
    data: {
      id,
    },
  })
}

export function retrySection(
  id: string,
) {
  return post({
    url: '/embedding/retrySection',
    data: {
      id,
    },
  })
}

function decodeEmbeddingProjectSecureFields(data: any) {
  data.name = decodeSecureField(data.name)
  if (data.memberInfo)
    data.memberInfo.nickname = decodeSecureField(data.memberInfo.nickname)
  data.publicList = PublicProjectStatus.OPEN === data.publicProject?.status
}

function decodeEmbeddingFileSecureFields(data: any) {
  data.fileName = decodeSecureField(data.fileName)
  data.content = decodeSecureField(data.content)
}

function decodeEmbeddingSectionSecureFields(data: any) {
  data.title = decodeSecureField(data.title)
  data.content = decodeSecureField(data.content)
}

function decodeEmbeddingQASecureFields(data: any) {
  data.question = decodeSecureField(data.question)
  data.answer = decodeSecureField(data.answer)
  data.title = decodeSecureField(data.title)
  data.prompt = decodeSecureField(data.prompt)
}
