declare namespace Card {
  interface CardMemberInfosResponse extends Api.BaseResponse {
    data: {
      [key: string]: {
        memberId: number;
        balance: number;
        balanceText: string;
      };
    };
  }

  interface MemberCardOrder {
    id: number;
    recordId: string;
    memberId: number;
    operationType: number;
    operationTypeText: string;
    businessType: number;
    businessTypeText: string;
    businessId: number;
    changeAmount: number;
    detailIds: number[];
    time: number;
    isDeduct: boolean;
    memberInfo: Member.MemberInfo;
  }

  type MemberCardOrderListResponse = {
    list: MemberCardOrder[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  interface CardType {
    id: number;
    name: string;
    amount: number;
    expireSeconds: number;
    enable: boolean;
    system: boolean;
    createTime: number;
  }

  interface Card {
    id: number;
    recordId: string;
    type: number;
    typeText: string;
    memberId: number;
    amount: number;
    amountText: string;
    leftAmount: number;
    leftAmountText: string;
    createTime: number;
    activationTime: number;
    expireTime: number;
    cardType: CardType;
    expired: boolean;
    memberInfo: Member.MemberInfo;
  }

  type CardListResponse = {
    list: Card[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}
