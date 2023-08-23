const management: AuthRoute.Route = {
  name: 'member',
  path: '/member',
  component: 'basic',
  children: [
    {
      name: 'member_list',
      path: '/member/list',
      component: 'self',
      meta: {
        title: '用户管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    }
  ],
  meta: {
    title: '用户管理',
    icon: 'carbon:cloud-service-management',
    order: 1
  }
};

export default management;
