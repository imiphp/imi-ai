import { request } from '../request';

/** 获取用户列表 */
export const fetchMemberList = async (search = '', status = 0, page = 1, limit = 15) => {
  return request.post<Member.MemberListResponse>('/admin/member/list', {
    search,
    status,
    page,
    limit
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
