const card: AuthRoute.Route = {
  name: 'card',
  path: '/card',
  component: 'basic',
  children: [
    {
      name: 'card_type_list',
      path: '/card/type/list',
      component: 'self',
      meta: {
        title: '卡类型管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'card_memberCardDetails',
      path: '/card/memberCardDetails',
      component: 'self',
      meta: {
        title: '用户交易明细',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'card_list',
      path: '/card/list',
      component: 'self',
      meta: {
        title: '用户卡包列表',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'card_details',
      path: '/card/details',
      component: 'self',
      meta: {
        title: '卡交易明细',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts',
        hide: true
      }
    }
  ],
  meta: {
    title: '卡包管理',
    icon: 'carbon:cloud-service-management',
    order: 1
  }
};

export default card;
