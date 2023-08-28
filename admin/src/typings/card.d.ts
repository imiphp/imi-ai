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
    memberInfo: {
      recordId: string;
      nickname: string;
    };
  }

  type MemberCardOrderListResponse = {
    list: MemberCardOrder[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}
