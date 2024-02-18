import { decodeSecureField } from '~/src/utils/crypto';
import { request } from '../request';

/** 获取支付订单列表 */
export const fetchPaymentOrderList = async (data: {
  search?: string;
  businessType?: number;
  memberId?: number;
  type?: number;
  channel?: number | string;
  secondaryChannelId?: number;
  tertiaryChannelId?: number;
  beginTime?: number;
  endTime?: number;
  page?: number;
  limit?: number;
}) => {
  return request.get<Payment.OrderResponse>('/admin/paymentOrder/list', {
    params: data
  });
};
