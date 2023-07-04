import sha512 from 'crypto-js/sha512'

export function getCurrentDate() {
  const date = new Date()
  const day = date.getDate()
  const month = date.getMonth() + 1
  const year = date.getFullYear()
  return `${year}-${month}-${day}`
}

export function formatByte(size: number, decimals = 2) {
  // 符合人类阅读习惯的数字大小单位
  const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
  const index = Math.floor(Math.log(size) / Math.log(1024))
  return `${(size / 1024 ** index).toFixed(decimals)} ${
    units[index]
  }`
}

export function hashPassword(password: string): string {
  return sha512(password).toString()
}
