import { request } from '../request';

/** 获取用户列表 */
export const fetchCardMemberInfos = async (memberIds: string[]) => {
  return request.get<Card.CardMemberInfosResponse>('/admin/card/memberInfos', {
    params: {
      memberIds: memberIds.join(',')
    }
  });
};
