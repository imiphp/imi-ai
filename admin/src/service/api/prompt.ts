import { request } from '../request';

export const fetchPromptCrawlerList = async () => {
  return request.get<Prompt.PromptCrawlerListResponse>('/admin/prompt/crawlerList');
};
