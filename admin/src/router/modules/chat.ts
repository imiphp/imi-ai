const chat: AuthRoute.Route = {
  name: 'chat',
  path: '/chat',
  component: 'basic',
  children: [
    {
      name: 'chat_list',
      path: '/chat/list',
      component: 'self',
      meta: {
        title: 'AI聊天管理',
        requiresAuth: true,
        icon: 'ic:round-manage-accounts'
      }
    },
    {
      name: 'chat_message_list',
      path: '/chat/message/list',
      component: 'self',
      meta: {
        title: 'AI聊天消息列表',
        requiresAuth: true,
        hide: true
      }
    }
  ],
  meta: {
    title: 'AI聊天管理',
    icon: 'carbon:cloud-service-management',
    order: 1
  }
};

export default chat;
