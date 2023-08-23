import { request } from '../request';

export const ENUM_ALL = { text: '全部', value: 0 };

export async function config() {
  return request.get('/config/public');
}

export async function enumValues(name?: string | string[]) {
  if (typeof name === 'object') name = name.join(',');
  return request.get('/admin/enum/values', {
    data: {
      name
    }
  });
}
