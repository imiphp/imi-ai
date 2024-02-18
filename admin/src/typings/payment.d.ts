declare namespace Payment {
  interface Order {
    id: number;
    typeTitle: string;
    businessTypeTitle: string;
    createTime: number;
    channelTitle: string;
    secondaryChannelTitle: string;
    tertiaryChannelTitle: string;
    type: number;
    channelId: number;
    secondaryChannelId: number;
    tertiaryChannelId: number;
    tradeNo: string;
    channelTradeNo: string;
    secondaryTradeNo: string;
    tertiaryTradeNo: string;
    businessType: number;
    businessId: number;
    memberId: number;
    amount: number;
    leftAmount: number;
    remark: string;
    payTime: number;
    notifyTime: number;
    memberInfo: Member.MemberInfo;
  }

  type OrderResponse = {
    list: Order[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}
