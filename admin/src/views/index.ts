import type { RouteComponent } from 'vue-router';

export const views: Record<
  PageRoute.LastDegreeRouteKey,
  RouteComponent | (() => Promise<{ default: RouteComponent }>)
> = {
  403: () => import('./_builtin/403/index.vue'),
  404: () => import('./_builtin/404/index.vue'),
  500: () => import('./_builtin/500/index.vue'),
  'constant-page': () => import('./_builtin/constant-page/index.vue'),
  login: () => import('./_builtin/login/index.vue'),
  'not-found': () => import('./_builtin/not-found/index.vue'),
  about: () => import('./about/index.vue'),
  chat_list: () => import('./chat/list/index.vue'),
  chat_message_list: () => import('./chat/message_list/index.vue'),
  dashboard_analysis: () => import('./dashboard/analysis/index.vue'),
  exception_403: () => import('./exception/403/index.vue'),
  exception_404: () => import('./exception/404/index.vue'),
  exception_500: () => import('./exception/500/index.vue'),
  management_admin_member: () => import('./management/admin_member/index.vue'),
  member_list: () => import('./member/list/index.vue')
};
