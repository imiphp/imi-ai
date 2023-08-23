/**
 * 获取当前环境模式下的请求服务的配置
 * @param env 环境
 */
export function getServiceEnvConfig(env: ImportMetaEnv): ServiceEnvConfigWithProxyPattern {
  const { VITE_API_URL } = env;

  return {
    url: VITE_API_URL,
    proxyPattern: '/proxy-pattern'
  };
}
