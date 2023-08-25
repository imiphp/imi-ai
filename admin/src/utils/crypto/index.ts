import CryptoJS from 'crypto-js';
import { inflate } from 'pako';

const CryptoSecret = '__CryptoJS_Secret__';

/**
 * 加密数据
 * @param data - 数据
 */
export function encrypt(data: any) {
  const newData = JSON.stringify(data);
  return CryptoJS.AES.encrypt(newData, CryptoSecret).toString();
}

/**
 * 解密数据
 * @param cipherText - 密文
 */
export function decrypt(cipherText: string) {
  const bytes = CryptoJS.AES.decrypt(cipherText, CryptoSecret);
  const originalText = bytes.toString(CryptoJS.enc.Utf8);
  if (originalText) {
    return JSON.parse(originalText);
  }
  return null;
}

export function decodeSecureField(value: string): string {
  try {
    const input = Uint8Array.from(window.atob(value), m => m.codePointAt(0) ?? 0);
    return inflate(input, { raw: true, to: 'string' });
  } catch (err) {
    window.console.log(err);
    return '';
  }
}
