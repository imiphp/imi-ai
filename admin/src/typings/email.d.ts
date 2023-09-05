declare namespace Email {
  type EmailBlackListResponse = {
    list: string[];
    total: number;
  } & Api.BaseResponse;
}
