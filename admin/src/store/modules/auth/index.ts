import { unref, nextTick } from 'vue';
import { defineStore } from 'pinia';
import { router } from '@/router';
import { fetchLogin } from '@/service';
import { useRouterPush } from '@/composables';
import { localStg } from '@/utils';
import { $t } from '@/locales';
import { hashPassword } from '~/src/utils/auth';
import { useTabStore } from '../tab';
import { useRouteStore } from '../route';
import { getToken, getUserInfo, clearAuthStorage } from './helpers';

interface AuthState {
  /** 用户信息 */
  userInfo: Auth.UserInfo;
  /** 用户token */
  token: string;
  /** 登录的加载状态 */
  loginLoading: boolean;
}

export const useAuthStore = defineStore('auth-store', {
  state: (): AuthState => ({
    userInfo: getUserInfo(),
    token: getToken(),
    loginLoading: false
  }),
  getters: {
    /** 是否登录 */
    isLogin(state) {
      return Boolean(state.token);
    }
  },
  actions: {
    /** 重置auth状态 */
    resetAuthStore() {
      const { toLogin } = useRouterPush(false);
      const { resetTabStore } = useTabStore();
      const { resetRouteStore } = useRouteStore();
      const route = unref(router.currentRoute);

      clearAuthStorage();
      this.$reset();

      if (route.meta.requiresAuth) {
        toLogin();
      }

      nextTick(() => {
        resetTabStore();
        resetRouteStore();
      });
    },
    /**
     * 处理登录后成功或失败的逻辑
     */
    async handleActionAfterLogin(response: ApiAuth.LoginResponse) {
      const route = useRouteStore();
      const { toLoginRedirect } = useRouterPush(false);

      const loginSuccess = await this.loginByToken(response.token);

      if (loginSuccess) {
        await route.initAuthRoute();

        // 成功后把用户信息存储到缓存中
        localStg.set('userInfo', response.member);

        // 更新状态
        this.userInfo = response.member;

        // 跳转登录后的地址
        toLoginRedirect();

        // 登录成功弹出欢迎提示
        if (route.isInitAuthRoute) {
          window.$notification?.success({
            title: $t('page.login.common.loginSuccess'),
            content: $t('page.login.common.welcomeBack', { nickname: this.userInfo.nickname }),
            duration: 3000
          });
        }

        return;
      }

      // 不成功则重置状态
      this.resetAuthStore();
    },
    /**
     * 根据token进行登录
     */
    async loginByToken(token: string) {
      // 先把token存储到缓存中(后面接口的请求头需要token)
      localStg.set('token', token);

      return true;
    },

    /**
     * 登录
     * @param account - 用户名
     * @param password - 密码
     */
    async login(account: string, password: string, vcode: string, vcodeToken: string) {
      this.loginLoading = true;
      const { data } = await fetchLogin(account, hashPassword(password), vcode, vcodeToken);
      if (data) {
        await this.handleActionAfterLogin(data);
      }
      this.loginLoading = false;
    }
  }
});
