import pako from 'pako'
import type { AxiosError, AxiosProgressEvent, AxiosResponse, GenericAbortSignal } from 'axios'
import axios from 'axios'
import service, { cancelAll } from './axios'
import { useAuthStore, useUserStore } from '@/store'

export interface HttpOption {
  url: string
  data?: any
  method?: string
  headers?: any
  onDownloadProgress?: (progressEvent: AxiosProgressEvent) => void
  signal?: GenericAbortSignal
  beforeRequest?: () => void
  afterRequest?: () => void
  failHandler?: (res: AxiosResponse<Response>) => void
  apiFailHandler?: (res: AxiosResponse<Response>) => void
}

export interface Response {
  code: number
  message: string | null
  [x: string]: any
}

function http(
  { url, data, method, headers, onDownloadProgress, signal, beforeRequest, afterRequest, apiFailHandler }: HttpOption,
) {
  const successHandler = (res: AxiosResponse<Response>) => {
    afterRequest?.()

    if (res.data.code === 0 || typeof res.data === 'string')
      return res.data

    if (apiFailHandler)
      apiFailHandler(res)
    else
      dialogApiFailHandler(res)

    switch (res.data.code) {
      case 10001:
        // 跳转到登录
        cancelAll()
        useAuthStore().removeToken()
        useAuthStore().setLoginRedirectUrl(location.href)
        useUserStore().resetUserInfo()
        window.$router.replace({ name: 'Login' })
        break
      case 11001:
        // 跳转到激活卡
        window.$router.replace({ name: 'Card', params: { tab: 'activation' } })
        break
    }

    return Promise.reject(res.data)
  }

  const failHandler = (error: AxiosError<any>) => {
    afterRequest?.()

    if (!axios.isCancel(error))
      dialogFailHandler(error)
    throw new Error(error?.response?.data?.message || 'Network Error')
  }

  beforeRequest?.()

  method = method || 'GET'

  const params = Object.assign(typeof data === 'function' ? data() : data ?? {}, {})

  return method === 'GET'
    ? service.get(url, { params, signal, onDownloadProgress }).then(successHandler, failHandler)
    : service.post(url, params, { headers, signal, onDownloadProgress }).then(successHandler, failHandler)
}

export function get(
  { url, data, method = 'GET', onDownloadProgress, signal, beforeRequest, afterRequest, apiFailHandler }: HttpOption,
): Promise<Response> {
  return http({
    url,
    method,
    data,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
    apiFailHandler,
  })
}

export function post(
  { url, data, method = 'POST', headers, onDownloadProgress, signal, beforeRequest, afterRequest, apiFailHandler }: HttpOption,
): Promise<Response> {
  return http({
    url,
    method,
    data,
    headers,
    onDownloadProgress,
    signal,
    beforeRequest,
    afterRequest,
    apiFailHandler,
  })
}

export default post

export function dialogApiFailHandler(res: AxiosResponse<Response>): void {
  window.$dialog?.error({
    title: '错误',
    content: res.data.message || 'Error',
    positiveText: '确定',
  })
}

export function dialogFailHandler(res: AxiosError<any>): void {
  window.$dialog?.error({
    title: '错误',
    content: res?.response?.data?.message || 'Network Error',
    positiveText: '确定',
  })
}

export function decodeSecureField(value: string): string {
  try {
    const input = Uint8Array.from(window.atob(value), m => m.codePointAt(0) ?? 0)
    return pako.inflate(input, { raw: true, to: 'string' })
  }
  catch (err) {
    window.console.log(err)
    return ''
  }
}
