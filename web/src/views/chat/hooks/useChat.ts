import { useChatStore } from '@/store'

export function useChat() {
  const chatStore = useChatStore()

  const getChatByUuidAndIndex = (id: string, index: number) => {
    return chatStore.getChatByUuidAndIndex(id, index)
  }

  const addChat = (id: string, chat: Chat.Chat) => {
    chatStore.addChatByUuid(id, chat)
  }

  const updateChat = (id: string, index: number, chat: Chat.Chat) => {
    chatStore.updateChatByUuid(id, index, chat)
  }

  const updateChatSome = (id: string, index: number, chat: Partial<Chat.Chat>) => {
    chatStore.updateChatSomeByUuid(id, index, chat)
  }

  const deleteChatByUuid = (id: string, index: number) => {
    chatStore.deleteChatByUuid(id, index)
  }

  return {
    addChat,
    updateChat,
    updateChatSome,
    getChatByUuidAndIndex,
    deleteChatByUuid,
  }
}
