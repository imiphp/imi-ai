import { request } from '../request';

/**
 * 获取验证码
 */
export function fetchVcode() {
  return request.get<VCode.VCodeResponse>('/vcode/get');
}
