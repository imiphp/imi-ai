import { request } from '../request';

export const fetchOpenAIClientList = async () => {
  return request.get<OpenAI.ClientListResponse>('/admin/openai/clientList');
};
