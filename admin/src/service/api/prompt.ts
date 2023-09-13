import { request } from '../request';

export const fetchPromptCrawlerList = async () => {
  return request.get<Prompt.PromptCrawlerListResponse>('/admin/prompt/crawlerList');
};

export const fetchPromptCategoryList = async () => {
  return request.get<Prompt.PromptCategoryListResponse>('/admin/promptCategory/list');
};

export const fetchPromptList = async (type: number, categoryIds: number[], search = '', page = 1, limit = 15) => {
  return request.get<Prompt.PromptListResponse>('/admin/prompt/list', {
    params: {
      type,
      categoryIds: categoryIds.join(','),
      search,
      page,
      limit
    }
  });
};

export const createPrompt = async (
  type: number,
  categoryIds: number[],
  title: string,
  description: string,
  prompt: string,
  firstMessageContent: string,
  index: number,
  config: any,
  formConfig: any[]
) => {
  return request.post<Api.BaseResponse>('/admin/prompt/create', {
    type,
    categoryIds,
    title,
    description,
    prompt,
    firstMessageContent,
    index,
    config,
    formConfig
  });
};

export const updatePrompt = async (
  id: number,
  data: {
    categoryIds?: number[];
    title?: string;
    description?: string;
    prompt?: string;
    firstMessageContent?: string;
    index?: number;
    config?: any;
    formConfig?: any[];
  }
) => {
  return request.post<Api.BaseResponse>('/admin/prompt/update', {
    id,
    ...data
  });
};

export const deletePrompt = async (id: number) => {
  return request.post<Api.BaseResponse>('/admin/prompt/delete', {
    id
  });
};

export const createPromptCategory = async (title: string, index: number) => {
  return request.post<Api.BaseResponse>('/admin/promptCategory/create', {
    title,
    index
  });
};

export const updatePromptCategory = async (id: number, data: { title?: string; index?: number }) => {
  return request.post<Api.BaseResponse>('/admin/promptCategory/update', {
    id,
    ...data
  });
};

export const deletePromptCategory = async (id: number) => {
  return request.post<Api.BaseResponse>('/admin/promptCategory/delete', {
    id
  });
};
