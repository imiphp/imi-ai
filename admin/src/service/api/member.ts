import { decodeSecureField } from '~/src/utils/crypto';
import { request } from '../request';

/** 获取用户列表 */
export const fetchMemberList = async (search = '', status = 0, page = 1, limit = 15) => {
  const response = await request.get<Member.MemberListResponse>('/admin/member/list', {
    params: {
      search,
      status,
      page,
      limit
    }
  });

  response.data?.list.forEach(item => {
    item.nickname = decodeSecureField(item.nickname);
  });

  return response;
};

export const createMember = async (
  id: number,
  data: { nickname?: string; email?: string; password?: string; status?: number }
) => {
  return request.post<Api.BaseResponse>('/admin/member/create', {
    id,
    ...data
  });
};

export const updateMember = async (
  id: number,
  data: { nickname?: string; email?: string; password?: string; status?: number }
) => {
  return request.post<Api.BaseResponse>('/admin/member/update', {
    id,
    ...data
  });
};
