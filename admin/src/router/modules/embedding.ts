const embedding: AuthRoute.Route = {
  name: 'embedding',
  path: '/embedding',
  component: 'basic',
  children: [
    {
      name: 'embedding_list',
      path: '/embedding/list',
      component: 'self',
      meta: {
        title: '模型训练管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'embedding_file_list',
      path: '/embedding/file/list',
      component: 'self',
      meta: {
        title: '模型文件管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts',
        hide: true
      }
    },
    {
      name: 'embedding_qa_list',
      path: '/embedding/qa/list',
      component: 'self',
      meta: {
        title: '模型对话管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'embedding_public_list',
      path: '/embedding/public/list',
      component: 'self',
      meta: {
        title: '公开项目管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    }
  ],
  meta: {
    title: '模型训练管理',
    icon: 'carbon:cloud-service-management',
    order: 1
  }
};

export default embedding;
