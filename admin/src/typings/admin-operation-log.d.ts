declare namespace AdminOperationLog {
  interface Log {
    id: number;
    memberId: number;
    object: string;
    objectText: string;
    status: number;
    statusText: string;
    message: string;
    ip: string;
    time: number;
    memberInfo: Member.MemberInfo;
  }

  type LogListResponse = {
    list: Log[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}
