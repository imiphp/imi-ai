import { defineStore } from 'pinia'
import { getLocalState } from './helper'
import { router } from '@/router'
import { deleteSession, sessionList } from '@/api'

export const enum QAStatus {
  ASK = 1,
  ANSWER = 2,
}

export interface ModelConfig {
  enable: boolean
  inputTokenMultiple: string
  outputTokenMultiple: string
}

export function defaultChatSetting(): Chat.ChatSetting {
  return {
    model: 'gpt-3.5-turbo',
    temperature: 1,
    top_p: 1,
    presence_penalty: 0,
    frequency_penalty: 0,
  }
}

export const useChatStore = defineStore('chat-store', {
  state: (): Chat.ChatState => getLocalState(),

  getters: {
    getChatHistoryByCurrentActive(state: Chat.ChatState): Chat.History | null {
      const index = state.history.findIndex(item => item.id === state.active)
      if (index !== -1)
        return state.history[index]
      return null
    },

    getChatByUuid(state: Chat.ChatState) {
      return (id?: string) => {
        if (id)
          return state.chat.find(item => item.id === id)?.data ?? []
        return state.chat.find(item => item.id === state.active)?.data ?? []
      }
    },
  },

  actions: {
    async loadChatList() {
      const response = await sessionList(1, 1000)
      const historys = []
      for (const item of response.list) {
        const history = { ...item }
        history.id = history.recordId
        historys.push(history)
      }
      this.history = historys
    },

    setUsingContext(context: boolean) {
      this.usingContext = context
      this.recordState()
    },

    addHistory(history: Chat.History, chatData: Chat.Chat[] = [], reload = true) {
      this.history.unshift(history)
      this.chat.unshift({ id: history.id, data: chatData })
      this.active = history.id
      if (reload)
        this.reloadRoute(history.id, null)
    },

    updateHistory(id: string, edit: Partial<Chat.History>) {
      const index = this.history.findIndex(item => item.id === id)
      if (index !== -1) {
        this.history[index] = { ...this.history[index], ...edit }
        this.recordState()
      }
    },

    async deleteHistory(index: number) {
      const id = this.history[index].id
      await deleteSession(id)
      try {
        this.history.splice(index, 1)
        this.chat.splice(index, 1)

        if (this.history.length === 0) {
          this.active = null
          this.reloadRoute(undefined, null)
          return
        }

        if (index > 0 && index <= this.history.length) {
          const id = this.history[index - 1].id
          this.active = id
          this.reloadRoute(id, null)
          return
        }

        if (index === 0) {
          if (this.history.length > 0) {
            const id = this.history[0].id
            this.active = id
            this.reloadRoute(id, null)
          }
        }

        if (index > this.history.length) {
          const id = this.history[this.history.length - 1].id
          this.active = id
          this.reloadRoute(id, null)
        }
      }
      finally {
        await this.loadChatList()
      }
    },

    async deleteHistoryById(id: string, reload = true) {
      const index = this.history.findIndex(item => item.id === id)
      if (index !== -1) {
        this.history.splice(index, 1)
        this.chat.splice(index, 1)

        if (this.history.length === 0) {
          this.active = null
          if (reload)
            this.reloadRoute(undefined, null)
          return
        }

        if (index > 0 && index <= this.history.length) {
          const id = this.history[index - 1].id
          this.active = id
          if (reload)
            this.reloadRoute(id, null)
          return
        }

        if (index === 0) {
          if (this.history.length > 0) {
            const id = this.history[0].id
            this.active = id
            if (reload)
              this.reloadRoute(id, null)
          }
        }

        if (index > this.history.length) {
          const id = this.history[this.history.length - 1].id
          this.active = id
          if (reload)
            this.reloadRoute(id, null)
        }
      }
    },

    async setActive(id: string, prompt?: Chat.ChatStatePrompt | null) {
      this.active = id
      if (prompt !== null)
        this.prompt = prompt

      return await this.reloadRoute(id, this.prompt)
    },

    getChatByUuidAndIndex(id: string, index: number) {
      if (!id || id === '') {
        if (this.chat.length)
          return this.chat[0].data[index]
        return null
      }
      const chatIndex = this.chat.findIndex(item => item.id === id)
      if (chatIndex !== -1)
        return this.chat[chatIndex].data[index]
      return null
    },

    addChatByUuid(id: string, chat: Chat.Chat) {
      if (!id || id === '') {
        if (this.history.length === 0) {
          const id = Date.now().toString()
          this.history.push({
            id,
            title: chat.message,
            isEdit: false,
            createTime: 0,
            updateTime: 0,
            qaStatus: undefined,
            tokens: 0,
          })
          this.chat.push({ id, data: [chat] })
          this.active = id
          this.recordState()
        }
        else {
          this.chat[0].data.push(chat)
          if (this.history[0].title === 'New Chat')
            this.history[0].title = chat.message
          this.recordState()
        }
      }

      let index = this.chat.findIndex(item => item.id === id)
      if (index !== -1 && this.chat[index])
        this.chat[index].data.push(chat)

      index = this.history.findIndex(item => item.id === id)
      if (index !== -1 && this.history[index]) {
        if (this.history[index].title === 'New Chat')
          this.history[index].title = chat.message
      }
      this.recordState()
    },

    setChatsById(id: string, chats: Chat.Chat[]) {
      if (!id || id === '')
        return

      const chatIndex = this.chat.findIndex(item => item.id === id)
      if (chatIndex !== -1)
        this.chat[chatIndex].data = chats
      else
        this.chat.push({ id, data: chats })

      this.recordState()
    },

    updateChatByUuid(id: string, index: number, chat: Chat.Chat) {
      if (!id || id === '') {
        if (this.chat.length) {
          this.chat[0].data[index] = chat
          this.recordState()
        }
        return
      }

      const chatIndex = this.chat.findIndex(item => item.id === id)
      if (chatIndex !== -1) {
        this.chat[chatIndex].data[index] = chat
        this.recordState()
      }
    },

    updateChatSomeByUuid(id: string, index: number, chat: Partial<Chat.Chat>) {
      if (!id || id === '') {
        if (this.chat.length) {
          this.chat[0].data[index] = { ...this.chat[0].data[index], ...chat }
          this.recordState()
        }
        return
      }

      const chatIndex = this.chat.findIndex(item => item.id === id)
      if (chatIndex !== -1) {
        this.chat[chatIndex].data[index] = { ...this.chat[chatIndex].data[index], ...chat }
        this.recordState()
      }
    },

    deleteChatByUuid(id: string, index: number) {
      if (!id || id === '') {
        if (this.chat.length) {
          this.chat[0].data.splice(index, 1)
          this.recordState()
        }
        return
      }

      const chatIndex = this.chat.findIndex(item => item.id === id)
      if (chatIndex !== -1) {
        this.chat[chatIndex].data.splice(index, 1)
        this.recordState()
      }
    },

    clearChatByUuid(id: string) {
      if (!id || id === '') {
        if (this.chat.length) {
          this.chat[0].data = []
          this.recordState()
        }
        return
      }

      const index = this.chat.findIndex(item => item.id === id)
      if (index !== -1) {
        this.chat[index].data = []
        this.recordState()
      }
    },

    async reloadRoute(id?: string, prompt?: Chat.ChatStatePrompt | null) {
      this.recordState()
      await router.push({ name: 'Chat', params: { id }, query: { usePrompt: (prompt === null ? this.prompt : prompt) ? '1' : undefined } })
    },

    recordState() {
      // setLocalState(this.$state)
    },
  },
})
