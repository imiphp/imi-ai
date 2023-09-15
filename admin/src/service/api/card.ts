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
  activationed: boolean | undefined = undefined,
  expired: boolean | undefined = undefined,
  paying: boolean | undefined = undefined,
  page = 1,
  limit = 15
) => {
  let activationedParam;
  if (activationed !== undefined) {
    activationedParam = activationed ? '1' : '0';
  }
  let expiredParam;
  if (expired !== undefined) {
    expiredParam = expired ? '1' : '0';
  }
  let payingParam;
  if (paying !== undefined) {
    payingParam = paying ? '1' : '0';
  }
  return request.get<Card.CardListResponse>('/admin/card/list', {
    params: {
      memberId,
      type,
      activationed: activationedParam,
      expired: expiredParam,
      paying: payingParam,
      page,
      limit
    }
  });
};

export const fetchCardDetails = async (
  cardId = 0,
  operationType = 0,
  businessType = 0,
  beginTime = 0,
  endTime = 0,
  page = 1,
  limit = 15
) => {
  return request.get<Card.CardDetailListResponse>('/admin/card/details', {
    params: {
      cardId,
      operationType,
      businessType,
      beginTime,
      endTime,
      page,
      limit
    }
  });
};

export const fetchCardTypeList = async (enable: boolean | undefined = undefined, page = 1, limit = 15) => {
  let enableParam;
  if (enable !== undefined) {
    enableParam = enable ? '1' : '0';
  }
  return request.get<Card.CardTypeListResponse>('/admin/card/type/list', {
    params: {
      enable: enableParam,
      page,
      limit
    }
  });
};

export const createCardType = async (
  name: string,
  amount: number,
  expireSeconds: number,
  enable = true,
  memberActivationLimit = 0
) => {
  return request.post<Api.BaseResponse>('/admin/card/type/create', {
    name,
    amount,
    expireSeconds,
    enable,
    memberActivationLimit
  });
};

export const updateCardType = async (
  id: number,
  data: { name?: string; amount?: number; expireSeconds?: number; enable?: boolean }
) => {
  return request.post<Api.BaseResponse>('/admin/card/type/update', {
    id,
    ...data
  });
};

export const generateCard = async (type: number, count: number, remark = '', paying = false) => {
  return request.post<Card.GenerateCardResponse>('/admin/card/generate', {
    type,
    count,
    remark,
    paying
  });
};

export const updateCard = async (id: number, data: { remark?: string; enable?: boolean; paying?: boolean }) => {
  return request.post<Api.BaseResponse>('/admin/card/update', {
    id,
    ...data
  });
};
