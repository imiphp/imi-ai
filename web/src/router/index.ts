import type { App } from 'vue'
import type { RouteRecordRaw } from 'vue-router'
import { createRouter, createWebHashHistory } from 'vue-router'
import { setupPageGuard } from './permission'
import { CommonLayout } from '@/views/common/layout'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Root',
    component: CommonLayout,
    redirect: '/',
    children: [
      // 首页
      {
        path: '/',
        name: 'Home',
        component: () => import('@/views/home/index.vue'),
      },
      // Chat
      {
        path: '/chat/:id?',
        name: 'Chat',
        component: () => import('@/views/chat/index.vue'),
        meta: {
          title: 'AI聊天',
        },
      },
      // Embedding
      {
        path: '/embedding/',
        name: 'Embedding',
        component: () => import('@/views/embedding/index.vue'),
        meta: {
          title: '模型训练',
        },
      },
      {
        path: '/embedding/:id',
        name: 'ViewEmbeddingProject',
        component: () => import('@/views/embedding/viewProject.vue'),
        meta: {
          title: '模型训练',
        },
      },
      {
        path: '/embedding/chat/:id',
        name: 'EmbeddingChat',
        component: () => import('@/views/embedding/chat.vue'),
        meta: {
          title: '模型对话',
        },
      },
      // auth
      {
        path: '/auth/login',
        name: 'Login',
        component: () => import('@/views/auth/login.vue'),
        meta: {
          title: '登录',
        },
      },
      {
        path: '/auth/register',
        name: 'Register',
        component: () => import('@/views/auth/register.vue'),
        meta: {
          title: '注册',
        },
      },
      {
        path: '/auth/verifyRegisterEmail/:email/:token/:verifyToken',
        name: 'VerifyRegisterEmail',
        component: () => import('@/views/auth/verifyRegisterEmail.vue'),
        meta: {
          title: '验证注册邮箱',
        },
      },
    ],
  },

  {
    path: '/404',
    name: '404',
    component: () => import('@/views/exception/404/index.vue'),
  },

  {
    path: '/500',
    name: '500',
    component: () => import('@/views/exception/500/index.vue'),
  },

  {
    path: '/:pathMatch(.*)*',
    name: 'notFound',
    redirect: '/404',
  },
]

export const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior: () => ({ left: 0, top: 0 }),
})

// 页面标题
router.beforeEach(async (to, from, next) => {
  if (to.meta.title)
    document.title = to.meta.title as string

  next()
})

setupPageGuard(router)

export async function setupRouter(app: App) {
  app.use(router)
  await router.isReady()
}
