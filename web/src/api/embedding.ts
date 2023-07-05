import type { AxiosProgressEvent, GenericAbortSignal } from 'axios'
import { get, post } from '@/utils/request'

export function projectList(
  page = 1,
  limit = 15,
) {
  return get({
    url: '/embedding/openai/projectList',
    data: {
      page,
      limit,
    },
  })
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
  name: string,
) {
  return post({
    url: '/embedding/openai/updateProject',
    data: {
      id,
      name,
    },
  })
}

export function getProject(
  id: string,
) {
  return get({
    url: '/embedding/openai/getProject',
    data: {
      id,
    },
  })
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

export function sectionList(
  projectId: string,
  fileId: string,
) {
  return get({
    url: '/embedding/openai/sectionList',
    data: {
      projectId,
      fileId,
    },
  })
}

export function chatList(
  id: string,
  page = 1,
  limit = 15,
) {
  return get({
    url: '/embedding/openai/chatList',
    data: {
      id,
      page,
      limit,
    },
  })
}

export function sendEmbeddingMessage(
  projectId: string,
  question: string,
  config?: any,
) {
  return post({
    url: '/embedding/openai/sendMessage',
    data: {
      projectId,
      question,
      config,
    },
  })
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
