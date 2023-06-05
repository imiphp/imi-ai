# 生产环境禁用热更新
@app.beans.hotUpdate.status=0
# 生产环境禁用调试
APP_DEBUG=false

# 服务监听ip
APP_HOST=0.0.0.0
# 服务端口
APP_PORT=12333
# 服务 Worker 进程数量
APP_WORKER_NUM=2

# MySQL 配置
APP_MYSQL_HOST=127.0.0.1
APP_MYSQL_PORT=3306
APP_MYSQL_USERNAME=root
APP_MYSQL_PASSWORD=root
APP_MYSQL_DATABASE=db_imi_ai

# Redis 配置
APP_REDIS_HOST=127.0.0.1
APP_REDIS_PORT=6379

# OpenAI 配置
OPENAI_KEY=""
# OpenAI 接口地址，支持自搭建代理
# OPENAI_BASE_URL="api.openai.com/v1"
# HTTP 代理
# OPENAI_PROXY="127.0.0.1:12345"

# id 加密盐，改成你自己的
AI_ID_SALT=imi_ai
