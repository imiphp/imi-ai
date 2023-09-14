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
  if (time) {
    str += `${time}秒`;
  }
  return str;
}
