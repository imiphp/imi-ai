import { decodeSecureField } from '~/src/utils/crypto';
import { request } from '../request';

export const fetchChatSessionList = async (search = '', type = 0, page = 1, limit = 15) => {
  const response = request.get<Chat.SessionListResponse>('/admin/chat/list', {
    params: {
      search,
      type,
      page,
      limit
    }
  });

  (await response).data?.list.forEach(item => {
    item.title = decodeSecureField(item.title);
    item.prompt = decodeSecureField(item.prompt);
    item.memberInfo.nickname = decodeSecureField(item.memberInfo.nickname);
  });

  return response;
};

export const deleteChatSession = async (id: number) => {
  return request.post<Api.BaseResponse>('/admin/chat/delete', {
    id
  });
};

export const fetchChatMessageList = async (sessionId: number, page = 1, limit = 15) => {
  const response = await request.get<Chat.MessageListResponse>('/admin/chat/messageList', {
    params: {
      sessionId,
      page,
      limit
    }
  });

  response.data?.list.forEach(item => {
    item.message = decodeSecureField(item.message);
  });

  return response;
};
