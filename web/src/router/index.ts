import type { App } from 'vue'
import type { RouteRecordRaw } from 'vue-router'
import { createRouter, createWebHashHistory } from 'vue-router'
import { setupPageGuard } from './permission'
import { ChatLayout } from '@/views/chat/layout'
import { EmbeddingLayout } from '@/views/embedding/layout'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'Root',
    component: ChatLayout,
    redirect: '/chat',
    children: [
      {
        path: '/chat/:id?',
        name: 'Chat',
        component: () => import('@/views/chat/index.vue'),
      },
    ],
  },
  {
    path: '/embedding',
    name: 'Embedding',
    component: EmbeddingLayout,
    redirect: '/embedding',
    children: [
      {
        path: '/embedding/',
        name: 'Embedding',
        component: () => import('@/views/embedding/index.vue'),
      },
      {
        path: '/embedding/:id',
        name: 'ViewEmbeddingProject',
        component: () => import('@/views/embedding/viewProject.vue'),
      },
      {
        path: '/embedding/chat/:id',
        name: 'EmbeddingChat',
        component: () => import('@/views/embedding/chat.vue'),
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

setupPageGuard(router)

export async function setupRouter(app: App) {
  app.use(router)
  await router.isReady()
}
