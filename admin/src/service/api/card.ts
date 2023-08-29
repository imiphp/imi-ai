import { request } from '../request';

export const fetchCardMemberInfos = async (memberIds: number[]) => {
  return request.get<Card.CardMemberInfosResponse>('/admin/card/memberInfos', {
    params: {
      memberIds: memberIds.join(',')
    }
  });
};

export const fetchMemberCardDetails = async (
  memberId = 0,
  operationType = 0,
  businessType = 0,
  beginTime = 0,
  endTime = 0,
  page = 1,
  limit = 15
) => {
  return request.get<Card.MemberCardOrderListResponse>('/admin/card/memberDetails', {
    params: {
      memberId,
      operationType,
      businessType,
      beginTime,
      endTime,
      page,
      limit
    }
  });
};

export const fetchCardList = async (
  memberId = 0,
  type = 0,
  expired: number | undefined = undefined,
  page = 1,
  limit = 15
) => {
  return request.get<Card.CardListResponse>('/admin/card/list', {
    params: {
      memberId,
      type,
      expired,
      page,
      limit
    }
  });
};
