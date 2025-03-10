VITE_BASE_URL=/

VITE_APP_NAME=imi AI 管理后台

VITE_APP_TITLE=imi AI 管理后台

VITE_APP_DESC=imi AI 管理后台

VITE_API_URL=http://localhost:12333

# Y=hash路由, N=history路由
VITE_HASH_ROUTE=Y

# 权限路由模式: static ｜ dynamic
VITE_AUTH_ROUTE_MODE=static

# 路由首页(根路由重定向), 用于static模式的权限路由，dynamic模式取决于后端返回的路由首页
VITE_ROUTE_HOME_PATH=/dashboard/analysis

# iconify图标作为组件的前缀
VITE_ICON_PREFIX=icon

# 本地SVG图标作为组件的前缀, 请注意一定要包含 VITE_ICON_PREFIX
# 格式 {VITE_ICON_PREFIX}-{本地图标集合名称}
VITE_ICON_LOCAL_PREFIX=icon-local
