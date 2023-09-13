const prompt: AuthRoute.Route = {
  name: 'prompt',
  path: '/prompt',
  component: 'basic',
  children: [
    {
      name: 'prompt_category_list',
      path: '/prompt/category/list',
      component: 'self',
      meta: {
        title: '提示语分类管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'prompt_list',
      path: '/prompt/list',
      component: 'self',
      meta: {
        title: '提示语管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    }
  ],
  meta: {
    title: '提示语管理',
    icon: 'carbon:cloud-service-management',
    order: 1
  }
};

export default prompt;
