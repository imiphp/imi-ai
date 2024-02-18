const payment: AuthRoute.Route = {
  name: 'payment',
  path: '/payment',
  component: 'basic',
  children: [
    {
      name: 'payment_order',
      path: '/payment/order',
      component: 'self',
      meta: {
        title: '支付订单',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    }
  ],
  meta: {
    title: '支付管理',
    icon: 'carbon:cloud-service-management',
    order: 1
  }
};

export default payment;
