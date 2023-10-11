export const rateLimitUnitOptions = [
  { label: '秒', value: 'second' },
  { label: '分钟', value: 'minute' },
  { label: '小时', value: 'hour' },
  { label: '天', value: 'day' },
  { label: '月', value: 'month' },
  { label: '年', value: 'year' },
  { label: '毫秒', value: 'millisecond' },
  { label: '微秒', value: 'microsecond' }
];

export function timespanHuman(seconds: number): string {
  let str = '';
  let time = seconds;
  const day = Math.floor(time / 86400);
  if (day) {
    str += `${day}天`;
  }
  time -= day * 86400;
  const hour = Math.floor(time / 3600);
  if (hour) {
    str += `${hour}小时`;
  }
  time -= hour * 3600;
  const minute = Math.floor(time / 60);
  if (minute) {
    str += `${minute}分`;
  }
  time -= minute * 60;
  if (time || str.length === 0) {
    str += `${time}秒`;
  }
  return str;
}

export function getRateLimitLabel(unit: string): string {
  const item = rateLimitUnitOptions.find(option => option.value === unit);
  return item ? item.label : '';
}
