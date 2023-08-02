import { defineStore } from 'pinia'
import { getLocalState } from './helper'

export const enum EmbeddingStatus {
  /**
   * 正在解压
   */
  EXTRACTING = 1,

  /**
   * 正在训练
   */
  TRAINING = 2,

  /**
   * 已完成
   */
  COMPLETED = 3,

  /**
   * 失败
   */
  FAILED = 4,
}

export const enum EmbeddingQAStatus {
  /**
   * 正在回答
   */
  ANSWERING = 1,

  /**
   * 成功
   */
  SUCCESS = 2,

  /**
   * 失败
   */
  FAILED = 3,
}

export const enum PublicProjectStatus {
  /**
   * 已开放
   */
  OPEN = 1,

  /**
   * 已关闭
   */
  CLOSED = 2,

  /**
   * 等待审核
   */
  WAIT_FOR_REVIEW = 3,

  /**
   * 审核失败
   */
  REVIEW_FAILED = 4,
}

export interface EmbeddingChatSetting {
  topSections: number
  similarity: number
  prompt: string
}

export function defaultEmbeddingSetting(): EmbeddingChatSetting {
  return {
    topSections: 5,
    similarity: 0,
    prompt: '',
  }
}

export const useEmbeddingStore = defineStore('embedding-store', {
  state: (): Embedding.State => getLocalState(),
})
