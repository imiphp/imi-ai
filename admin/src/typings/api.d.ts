// 后端接口返回的数据类型

/** 后端返回的用户权益相关类型 */
declare namespace ApiAuth {
  /** 返回的token和刷新token */
  interface Token {
    token: string;
    refreshToken: string;
  }
  /** 返回的用户信息 */
  type UserInfo = Auth.UserInfo;
  interface LoginResponse {
    token: string;
    member: UserInfo;
  }
}

/** 后端返回的路由相关类型 */
declare namespace ApiRoute {
  /** 后端返回的路由数据类型 */
  interface Route {
    /** 动态路由 */
    routes: AuthRoute.Route[];
    /** 路由首页对应的key */
    home: AuthRoute.AllRouteKey;
  }
}

declare namespace ApiUserManagement {
  interface User {
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
}

declare namespace Api {
  interface BaseResponse {
    code: number;
    message: string;
  }
}
