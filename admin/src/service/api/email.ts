import { request } from '../request';

export const fetchEmailBlackList = async (search = '', page = 1, limit = 15) => {
  return request.get<Email.EmailBlackListResponse>('/admin/email/blackList/list', {
    params: {
      search,
      page,
      limit
    }
  });
};

export const addEmailBlackList = async (domains: string[]) => {
  return request.post<Api.BaseResponse>('/admin/email/blackList/add', {
    domains
  });
};

export const removeEmailBlackList = async (domains: string[]) => {
  return request.post<Api.BaseResponse>('/admin/email/blackList/remove', {
    domains
  });
};
