# imi-ai

## 介绍

imi-ai 是一个 ChatGPT 开源项目，你可以用它方便地部署和使用 ChatGPT 功能。

后端基于 imi（PHP+Swoole），前端基于 [Chanzhaoyu/chatgpt-web](https://github.com/Chanzhaoyu/chatgpt-web)。

项目采用 MIT 协议开源，你可以方便地进行二次开发，并且可以用于商业用途。

## 示例

演示地址：建设中...

![pic001.jpg](doc/pic001.jpg)

## 功能列表

* [x] ChatGPT 聊天 AI（OpenAI）
* [ ] OpenAI 自定义数据训练
* [x] 服务端多会话储存和上下文逻辑
* [x] 渲染代码高亮
* [x] 渲染 LaTeX 公式
* [x] 保存消息到本地图片
* [x] 界面多语言
* [x] 界面主题
* [x] 提示词模型商店
* [ ] Midjourney 图片生成
* [ ] 用户注册和登录
* [ ] 计费系统
……

> 项目正在持续迭代中，欢迎所有人来贡献代码

## 开发调试

### 服务端

目录：`server`

#### 环境要求

* Linux / MacOS

* PHP >= 8.1

* Swoole >= v4.8.13 或 Swoole >= v5.0.3

> 建议直接使用 swoole-cli，可在 [Swoole Release 下载](https://github.com/swoole/swoole-src/releases)。

#### 安装依赖

`composer update`

#### 配置

复制 **.env.tpl** 改名为 **.env** 文件。

根据文件内注释修改对应的配置。

#### 数据库

首先创建 `db_imi_ai` 数据库，如果使用其它名称，需要在 `.env` 中修改。

执行生成表结构命令：

```shell
vendor/bin/imi-swoole generate/table
```

#### 运行

```shell
vendor/bin/imi-swoole swoole/start
```

### 前端

目录：`web`

#### 环境要求

`node` 需要 `^16 || ^18 || ^19` 版本（`node >= 14` 需要安装 [fetch polyfill](https://github.com/developit/unfetch#usage-as-a-polyfill)），使用 [nvm](https://github.com/nvm-sh/nvm) 可管理本地多个 `node` 版本

```shell
node -v
```

#### 安装依赖

```shell
npm install
```

> 也可以使用 yarn、pnpm 等。

#### 配置

编辑 **.env** 文件。

* `VITE_GLOB_API_URL`，服务端接口地址，如：`http://127.0.0.1:12333/`

* `VITE_APP_API_BASE_URL` 前端调试访问地址，如：`http://127.0.0.1:1002/`

#### 运行

```shell
npm run dev
```

## 生产部署

### 服务端

#### 配置

编辑 **.env** 文件。

必须的设置：

```env
# 生产环境禁用热更新
@app.beans.hotUpdate.status=0
# 生产环境禁用调试
APP_DEBUG=false
```

其它设置根据自身需要进行配置。

### 前端

#### 编译

```shell
npm run build-only
```

> `npm run build` 也可以，但会执行类型检查，不规范的代码编译不通过。

#### 编译结果

所有文件都在 `dist` 目录。
