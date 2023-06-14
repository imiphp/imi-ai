import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { get, post } from '@/utils/request'

export function projectList<T = any>(
  page = 1,
  limit = 15,
) {
  return get<T>({
    url: '/embedding/openai/projectList',
    data: {
      page,
      limit,
    },
  })
}

export function deleteProject<T = any>(
  id: string,
) {
  return post<T>({
    url: '/embedding/openai/deleteProject',
    data: {
      id,
    },
  })
}

export function updateProject<T = any>(
  id: string,
  name: string,
) {
  return post<T>({
    url: '/embedding/openai/updateProject',
    data: {
      id,
      name,
    },
  })
}

export function getProject<T = any>(
  id: string,
) {
  return get<T>({
    url: '/embedding/openai/getProject',
    data: {
      id,
    },
  })
}

export function assocFileList<T = any>(
  projectId: string,
) {
  return get<T>({
    url: '/embedding/openai/assocFileList',
    data: {
      projectId,
    },
  })
}

export function sectionList<T = any>(
  projectId: string,
  fileId: string,
) {
  return get<T>({
    url: '/embedding/openai/sectionList',
    data: {
      projectId,
      fileId,
    },
  })
}

export function chatList<T = any>(
  id: string,
  page = 1,
  limit = 15,
) {
  return get<T>({
    url: '/embedding/openai/chatList',
    data: {
      id,
      page,
      limit,
    },
  })
}

export function sendEmbeddingMessage<T = any>(
  projectId: string,
  question: string,
  config?: any,
) {
  return post<T>({
    url: '/embedding/openai/sendMessage',
    data: {
      projectId,
      question,
      config,
    },
  })
}

export function fetchEmbeddingChatAPIProcess<T = any>(
  id: string,
  params: {
    signal?: GenericAbortSignal
    onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void },
) {
  const data = {
    id,
  }

  return post<T>({
    url: '/embedding/openai/stream',
    data,
    signal: params.signal,
    onDownloadProgress: params.onDownloadProgress,
  })
}
