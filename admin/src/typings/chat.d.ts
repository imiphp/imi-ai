declare namespace Chat {
  interface Session {
    id: number;
    recordId: string;
    type: number;
    typeText: string;
    memberId: number;
    title: string;
    qaStatus: number;
    tokens: number;
    payTokens: number;
    config: any;
    prompt: string;
    ip: string;
    createTime: number;
    updateTime: number;
  }

  interface Message {
    id: number;
    recordId: string;
    sessionId: number;
    role: string;
    config: any;
    tokens: number;
    message: string;
    ip: string;
    beginTime: number;
    completeTime: number;
  }

  type SessionListResponse = {
    list: Session[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  type MessageListResponse = {
    list: Message[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}
