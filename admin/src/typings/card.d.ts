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
}
