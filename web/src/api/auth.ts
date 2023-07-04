import type { AxiosResponse } from 'axios'
import type { Response } from '@/utils/request'
import { post } from '@/utils/request'

export function sendRegisterEmail<T = any>(
  data: {
    email: string
    password: string
    vcodeToken: string
    vcode: string
  },
) {
  return post<T>({
    url: '/auth/email/sendRegisterEmail',
    data,
  })
}

export function emailRegister<T = any>(
  data: {
    email: string
    vcodeToken: string
    vcode: string
  }) {
  return post<T>({
    url: '/auth/email/register',
    data,
  })
}

export function verifyFromEmail<T = any>(
  email: string,
  token: string,
  verifyToken: string,
  apiFailHandler?: (res: AxiosResponse<Response>) => void,
) {
  return post<T>({
    url: '/auth/email/verifyFromEmail',
    data: {
      email,
      token,
      verifyToken,
    },
    apiFailHandler,
  })
}

export function login<T = any>(
  data: {
    account: string
    password: string
    vcodeToken: string
    vcode: string
  }) {
  return post<T>({
    url: '/auth/login',
    data,
  })
}
