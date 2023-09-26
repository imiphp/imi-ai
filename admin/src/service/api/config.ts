import { request } from '../request';

export async function getConfig() {
  return request.get('/admin/config/get');
}

export async function saveConfig(data: any) {
  return request.post<Api.BaseResponse>('/admin/config/save', {
    data
  });
}

export async function adminEnumValues(name?: string | string[]) {
  if (typeof name === 'object') name = name.join(',');
  return request.get('/admin/enum/values', {
    params: {
      name
    }
  });
}
