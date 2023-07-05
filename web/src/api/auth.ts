import type { AxiosResponse } from 'axios'
import type { Response } from '@/utils/request'
import { post } from '@/utils/request'

export function sendRegisterEmail(
  data: {
    email: string
    password: string
    vcodeToken: string
    vcode: string
  },
) {
  return post({
    url: '/auth/email/sendRegisterEmail',
    data,
  })
}

export function emailRegister(
  data: {
    email: string
    vcodeToken: string
    vcode: string
  }) {
  return post({
    url: '/auth/email/register',
    data,
  })
}

export function verifyFromEmail(
  email: string,
  token: string,
  verifyToken: string,
  apiFailHandler?: (res: AxiosResponse<Response>) => void,
) {
  return post({
    url: '/auth/email/verifyFromEmail',
    data: {
      email,
      token,
      verifyToken,
    },
    apiFailHandler,
  })
}

export function login(
  data: {
    account: string
    password: string
    vcodeToken: string
    vcode: string
  }) {
  return post({
    url: '/auth/login',
    data,
  })
}
