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
        props: route => (route.query),
        component: () => import('@/views/chat/index.vue'),
        meta: {
          title: 'AI聊天',
        },
      },
      // 提示语
      {
        path: '/prompt/store',
        name: 'PromptStore',
        component: () => import('@/views/prompt/store.vue'),
        meta: {
          title: '模型市场',
        },
      },
      // AI工具
      {
        path: '/aiTool/',
        name: 'AITool',
        component: () => import('@/views/ai_tool/index.vue'),
        meta: {
          title: 'AI工具',
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
        path: '/auth/register/:invitationCode?',
        name: 'Register',
        component: () => import('@/views/auth/register.vue'),
        meta: {
          title: '注册',
        },
      },
      {
        path: '/auth/forgot/:invitationCode?',
        name: 'Forgot',
        component: () => import('@/views/auth/forgot.vue'),
        meta: {
          title: '忘记密码',
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
      {
        path: '/auth/verifyForgotEmail/:email/:token/:verifyToken',
        name: 'VerifyForgotEmail',
        component: () => import('@/views/auth/verifyForgotEmail.vue'),
        meta: {
          title: '验证忘记密码邮箱',
        },
      },
      // 卡包
      {
        path: '/card/:tab?',
        name: 'Card',
        component: () => import('@/views/card/index.vue'),
        meta: {
          title: '我的卡包',
        },
      },
      // Tokenizer
      {
        path: '/tokenizer',
        name: 'Tokenizer',
        component: () => import('@/views/tokenizer/index.vue'),
        meta: {
          title: 'Token 分词器',
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
  if (to.name !== from.name)
    window.$loadingBar?.start()
  if (to.meta.title)
    document.title = `${to.meta.title as string} - ${import.meta.env.VITE_APP_TITLE as string}`

  next()
})

router.afterEach(() => {
  window.$loadingBar?.finish()
})

router.onError(() => {
  window.$loadingBar?.error()
})

setupPageGuard(router)

export async function setupRouter(app: App) {
  app.use(router)
  await router.isReady()
}
