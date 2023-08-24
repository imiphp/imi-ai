import { request } from '../request';

/** 获取用户列表 */
export const fetchAdminMemberList = async (search = '', status = 0, page = 1, limit = 15) => {
  return request.get<Admin.AdminMemberListResponse>('/admin/adminMember/list', {
    params: {
      search,
      status,
      page,
      limit
    }
  });
};

export const createAdminMember = async (account: string, nickname: string, password: string, status?: number) => {
  return request.post<Api.BaseResponse>('/admin/adminMember/create', {
    account,
    nickname,
    password,
    status
  });
};

export const updateAdminMember = async (
  id: number,
  account?: string,
  nickname?: string,
  password?: string,
  status?: number
) => {
  return request.post<Api.BaseResponse>('/admin/adminMember/update', {
    id,
    account,
    nickname,
    password,
    status
  });
};

export const deleteAdminMember = async (id: number) => {
  return request.post<Api.BaseResponse>('/admin/adminMember/delete', {
    id
  });
};
