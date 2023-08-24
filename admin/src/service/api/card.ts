import { request } from '../request';

/** 获取用户列表 */
export const fetchCardMemberInfos = async (memberIds: number[]) => {
  return request.get<Card.CardMemberInfosResponse>('/admin/card/memberInfos', {
    params: {
      memberIds: memberIds.join(',')
    }
  });
};
