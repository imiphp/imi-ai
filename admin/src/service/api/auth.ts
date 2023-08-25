import { request } from '../request';

/**
 * 登录
 * @param account - 用户名
 * @param password - 密码
 */
export function fetchLogin(account: string, password: string, vcode: string, vcodeToken: string) {
  return request.post<ApiAuth.LoginResponse>('/admin/auth/login', { account, password, vcode, vcodeToken });
}
