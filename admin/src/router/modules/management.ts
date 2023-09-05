const management: AuthRoute.Route = {
  name: 'management',
  path: '/management',
  component: 'basic',
  children: [
    {
      name: 'config',
      path: '/config',
      component: 'self',
      meta: {
        title: '系统设置',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'config_email_black_list',
      path: '/config/email/black/list',
      component: 'self',
      meta: {
        title: '邮箱域名黑名单设置',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'management_admin_member',
      path: '/management/admin/member',
      component: 'self',
      meta: {
        title: '后台用户管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'management_operation_log',
      path: '/management/operation/log',
      component: 'self',
      meta: {
        title: '后台操作日志',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    }
  ],
  meta: {
    title: '系统管理',
    i18nTitle: 'routes.management._value',
    icon: 'carbon:cloud-service-management',
    order: 114514
  }
};

export default management;
