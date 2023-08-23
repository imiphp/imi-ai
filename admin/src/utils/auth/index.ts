import sha512 from 'crypto-js/sha512';

export function formatByte(size: number, decimals = 2) {
  // 符合人类阅读习惯的数字大小单位
  const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  if (size === 0) return '0 B';
  const index = Math.floor(Math.log(size) / Math.log(1024));
  return `${(size / 1024 ** index).toFixed(decimals)} ${units[index]}`;
}

export function hashPassword(password: string): string {
  return sha512(password).toString();
}
