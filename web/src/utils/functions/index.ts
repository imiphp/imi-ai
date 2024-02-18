import sha512 from 'crypto-js/sha512'
import Decimal from 'decimal.js'

export function formatByte(size: number, decimals = 2) {
  // 符合人类阅读习惯的数字大小单位
  const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  if (size === 0)
    return '0 B'
  const index = Math.floor(Math.log(size) / Math.log(1024))
  return `${(size / 1024 ** index).toFixed(decimals)} ${
    units[index]
  }`
}

export function formatNumberToChinese(number: number, decimal = 3): string {
  const BYTE_UNITS = ['', '万', '亿']
  let i = 0
  while (number >= 10000) {
    number /= 10000
    ++i
  }
  let result
  if (decimal > 0) {
    const fixedNumber = number.toFixed(decimal)
    result = fixedNumber.substring(0, fixedNumber.length - 1)
  }
  else {
    result = parseInt(number.toString())
  }

  return result + (BYTE_UNITS[i] ?? '')
}

export function hashPassword(password: string): string {
  return sha512(password).toString()
}

export function convertCentToYuan(cent: number): string {
  return (new Decimal(cent)).div(100).toString()
}

export function timespanHuman(seconds: number): string {
  let str = ''
  let time = seconds
  const day = Math.floor(time / 86400)
  if (day)
    str += `${day}天`

  time -= day * 86400
  const hour = Math.floor(time / 3600)
  if (hour)
    str += `${hour}小时`

  time -= hour * 3600
  const minute = Math.floor(time / 60)
  if (minute)
    str += `${minute}分`

  time -= minute * 60
  if (time || str.length === 0)
    str += `${time}秒`

  return str
}
