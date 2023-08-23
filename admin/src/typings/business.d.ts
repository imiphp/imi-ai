/** 用户相关模块 */
declare namespace Auth {
  /**
   * 用户角色类型(前端静态路由用角色类型进行路由权限的控制)
   * - super: 超级管理员(该权限具有所有路由数据)
   * - admin: 管理员
   * - user: 用户
   */
  type RoleType = 'super' | 'admin' | 'user';

  /** 用户信息 */
  interface UserInfo {
    /** 用户id */
    id: string;
    /** 用户名 */
    account: string;
    /** 用户名 */
    nickname: string;
    /** 用户角色类型 */
    userRole: RoleType;
  }
}

declare namespace UserManagement {
  interface User extends ApiUserManagement.User {}

  /**
   * 用户状态
   * - 1: 启用
   * - 2: 禁用
   */
  type UserStatusKey = NonNullable<User['status']>;
}
