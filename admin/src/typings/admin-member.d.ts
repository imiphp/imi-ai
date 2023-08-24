declare namespace Admin {
  interface AdminMember {
    /** 用户id */
    id: number;
    /** 用户名 */
    account: string;
    /** 昵称 */
    nickname: string;
    /**
     * 用户状态
     * - 1: 启用
     * - 2: 禁用
     */
    status: number;
    statusText: string;
    createTime: number;
    lastLoginTime: number;
    lastLoginIp: string;
  }

  type AdminMemberListResponse = {
    list: AdminMember[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}
