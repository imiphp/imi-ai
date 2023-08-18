import type { AxiosResponse } from 'axios'
import type { Response } from '@/utils/request'
import { get, post } from '@/utils/request'

export function sendRegisterEmail(
  data: {
    email: string
    password: string
    vcodeToken: string
    vcode: string
    invitationCode?: string
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

export function authInfo() {
  return get({
    url: '/auth/info',
  })
}

export function updateProfile(
  data: {
    nickname: string
  }) {
  return post({
    url: '/profile/update',
    data,
  })
}

export function changePassword(oldPassword: string, newPassword: string) {
  return post({
    url: '/auth/changePassword',
    data: {
      oldPassword,
      newPassword,
    },
  })
}
