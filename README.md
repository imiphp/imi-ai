# imi-ai

<p align="center">
    <a href="https://www.imiphp.com" target="_blank">
        <img src="https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/logo.png" alt="imi" />
    </a>
</p>

[![Server test](https://github.com/imiphp/imi-ai/actions/workflows/server.yml/badge.svg)](https://github.com/imiphp/imi-ai/actions/workflows/server.yml)
[![Web test](https://github.com/imiphp/imi-ai/actions/workflows/web.yml/badge.svg)](https://github.com/imiphp/imi-ai/actions/workflows/web.yml)
[![Php Version](https://img.shields.io/badge/php-%3E=8.1-brightgreen.svg)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=5.0.3-brightgreen.svg)](https://github.com/swoole/swoole-src)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/imiphp/imi-ai/blob/master/LICENSE)

## 介绍

imi-ai 是一个 ChatGPT 开源项目，支持聊天、问答、写代码、写文章、做作业等功能。

项目架构合理，代码编写优雅，简单快速部署。前后端代码完全开源，不管是学习自用还是商用二开都很适合。

本项目现已支持 ChatGPT 聊天 AI 和 Embedding 模型训练对话。

项目采用 MIT 协议开源，你可以方便地进行二次开发，并且可以用于商业用途。

## 演示

公益演示地址：<https://ai.imiphp.com/> （注册送额度，付费可用 gpt-4、gpt-3.5-turbo-16k）

![演示](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/1.jpg)
![演示](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/2.jpg)
![演示](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/3.jpg)
![演示](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/4.jpg)

## 技术栈

后端基于 [imi](https://github.com/imiphp/imi) (PHP+Swoole)

前端基于 [Chanzhaoyu/chatgpt-web](https://github.com/Chanzhaoyu/chatgpt-web) (TypeScript+Vue3+Vite3+NaiveUI)

后台基于 [honghuangdc/soybean-admin](https://github.com/honghuangdc/soybean-admin) (TypeScript+Vue3+Vite3+NaiveUI)

## 功能列表

### 用户

* [x] 用户邮箱注册和登录
* [ ] 用户手机号注册和登录
* [ ] 微信登录（PC/公众号/小程序）

### 聊天 AI

* [x] ChatGPT 聊天 AI（OpenAI）
* [x] 支持设置提示语（Prompt）
* [x] 支持模型参数调参
* [x] 服务端多会话储存和上下文逻辑
* [x] 渲染代码高亮
* [x] 渲染 LaTeX 公式
* [x] 保存消息到本地图片
* [x] 提示词模型商店
* [x] 支持限流

### 模型训练

* [x] OpenAI 多文件（压缩）模型训练
* [x] OpenAI 单文件模型训练
* [x] 聊天 AI 回答问题（可用于问题解答和客服等场景）
* [ ] 搜索引擎，可定位文件
* [x] 支持解压文件（zip、rar、7z、xz、gz、bz、tar.*）
* [x] 支持解析 txt 文件
* [x] 支持解析 md 文件
* [x] 支持解析 docx 文件
* [x] 支持解析 pdf 文件
* [ ] 消息队列异步处理训练任务
* [x] 支持对话限流

### AI 生图

* [ ] OpenAI 图片生成
* [ ] Midjourney 图片生成

### 计费系统

* [x] Tokens 计费系统（卡）
* [x] 在线支付购买卡（接口层）
* [ ] 微信支付
* [ ] 支付宝支付
* [x] 输入卡号激活

### 支持的模型厂商

* [x] [OpenAI](https://openai.com/)
* [x] [Swoole AI](https://ai.swoole.com/)
* [x] [ChatGLM3](https://github.com/THUDM/ChatGLM3)
* [x] [Google Gemini](https://aistudio.google.com/)
* [x] [Qwen2](https://github.com/QwenLM/Qwen2) (Gitee AI Serverless API)
* [x] [Ollama](https://ollama.com/)

> 使用 Ollama 提供的类 OpenAI API，可以支持几乎所有开源模型私有化部署

### 其它

* [x] 设计文档
* [x] 接口文档
* [ ] Docker 支持
* [ ] 视频讲解教程

更多功能计划中……

> 项目正在持续迭代中，欢迎所有人来贡献代码

## 安装

### 服务端

**目录：**`server`

**环境要求：**

* Linux / MacOS，可用内存至少1G

* PHP >= 8.1（扩展：curl、gd、mbstring、pdo_mysql、redis、swoole）

* Swoole >= v5.0.3（必须启用 `--enable-openssl --enable-swoole-curl` 编译，模型训练需启用 [--enable-swoole-pgsql](https://wiki.swoole.com/#/environment?id=-enable-swoole-pgsql) 编译）

> 建议直接使用 swoole-cli，可在 [Swoole Release 下载](https://github.com/swoole/swoole-src/releases)。

* MySQL >= 8.0.17

* Redis

* PostgreSQL + [pgvector](https://github.com/pgvector/pgvector) （可选，使用模型训练必选，需为项目数据库启用扩展 `CREATE EXTENSION vector;`）

* 7-Zip，可选，但使用模型训练必选，用于解压文件。[下载](https://7-zip.org/download.html) 并将 `7zz` / `7zzs` 解压到 `/usr/bin/7z` 或 `/usr/local/bin/7z` 目录

* Pandoc，可选，安装后可支持 docx 文件模型训练。[下载](https://pandoc.org/installing.html)

* poppler-utils，可选，安装后可支持 pdf 文件模型训练。

**安装：**

```shell
# Debian/Ubuntu
apt install poppler-utils
# CentOS
yum install poppler-utils
# Alpine
apk add poppler-utils
```

**安装依赖：**

`composer update`

**生成证书：**

jwt 签名需要，必须生成自己的证书！

```shell
cd server/resource/jwt
openssl genrsa -out pri_key.pem 2048
openssl rsa -in pri_key.pem -pubout -out pub_key.pem
openssl genrsa -out admin_pri_key.pem 2048
openssl rsa -in admin_pri_key.pem -pubout -out admin_pub_key.pem
```

**配置文件：**

复制 **.env.tpl** 改名为 **.env** 文件。

根据文件内注释修改对应的配置。

**应用配置：**

后台-系统管理-系统设置

**导入 MySQL：**

首先创建 `db_imi_ai` 数据库，如果使用其它名称，需要在 `.env` 中修改。

执行生成表结构命令：

```shell
vendor/bin/imi-swoole generate/table
```

**导入 PostgreSQL：**

首先创建 `db_imi_ai` 数据库，如果使用其它名称，需要在 `.env` 中修改。

为 `db_imi_ai` 或你使用的数据库启用 `pgvector` 扩展：

```sql
CREATE EXTENSION vector;
```

导入 `pgsql.sql` 文件，创建表。

> 不使用模型训练功能，可以不配置 PostgreSQL。

**运行服务：**

```shell
vendor/bin/imi-swoole swoole/start
```

**生产环境：**

编辑 **.env** 文件。

必须的设置：

```env
# 生产环境禁用热更新
@app.beans.hotUpdate.status=0
# 生产环境禁用调试
APP_DEBUG=false
```

其它设置根据自身需要进行配置。

### 用户端H5

**目录：**`web`

**环境要求：**

`node` 需要 `^16 || ^18 || ^19` 版本（`node >= 14` 需要安装 [fetch polyfill](https://github.com/developit/unfetch#usage-as-a-polyfill)），使用 [nvm](https://github.com/nvm-sh/nvm) 可管理本地多个 `node` 版本

```shell
node -v
```

**安装依赖：**

```shell
npm install
```

> 也可以使用 yarn、pnpm 等。

**配置：**

复制 **.env.tpl** 改名为 **.env** 文件。

编辑 **.env** 文件。

* `VITE_GLOB_API_URL`，服务端接口地址，如：`http://127.0.0.1:12333/`

* `VITE_APP_API_BASE_URL` 前端调试访问地址，如：`http://127.0.0.1:3100/`

**开发调试：**

```shell
npm run dev
```

**生产环境：**

#### 编译

```shell
npm run build-only
```

> `npm run build` 也可以，但会执行类型检查，不规范的代码编译不通过。

#### 编译结果

所有文件都在 `dist` 目录，内部文件放到站点根目录。

### 管理后台

后台默认账号密码都是 `admin`

**目录：**`admin`

**环境要求：**

`node` 需要 `^16 || ^18 || ^19` 版本（`node >= 14` 需要安装 [fetch polyfill](https://github.com/developit/unfetch#usage-as-a-polyfill)），使用 [nvm](https://github.com/nvm-sh/nvm) 可管理本地多个 `node` 版本

```shell
node -v
```

**安装依赖：**

```shell
npm install
```

> 也可以使用 yarn、pnpm 等。

**配置：**

复制 **.env.tpl** 改名为 **.env** 文件。

编辑 **.env** 文件。

* `VITE_API_URL`，服务端接口地址，如：`http://127.0.0.1:12333`

**开发调试：**

```shell
npm run dev
```

**生产环境：**

#### 编译

```shell
npm run build
```

> `npm run build` 也可以，但会执行类型检查，不规范的代码编译不通过。

#### 编译结果

所有文件都在 `dist` 目录，内部文件放到站点根目录。

## 技术支持

可提供以下服务：项目搭建部署、技术咨询、定制开发等

**QQ群：** 17916227

**微信群：**

![微信](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/wechat.png)

## 赞助开发

imi-ai 是基于 MIT 协议完全开源的项目，为了能够更好地可持续发展，特推出赞助权益。

### 赞助等级

| 等级 | 价格 | 说明 |
| - | - | - |
| 白嫖用户 | 0 | 完整项目代码，免费用于商业用途，Github/QQ群/微信群交流提问 |
| 白银赞助 | ￥9.9 | 可获得设计文档，接口文档 |
| 黄金赞助 | ￥499/年 | 一对一问题咨询 |
| 铂金赞助 | ￥888/年 | 支持私有化部署模型，1次免费项目搭建部署，一对一问题咨询 |

### 权益

| 权益 | 白嫖用户 | 白银赞助 | 黄金赞助 | 铂金赞助 | 备注 |
| - | - | - | - | - | - |
| 完整项目代码 | ✔ | ✔ | ✔ | ✔ |  |
| 免费用于商业用途 | ✔ | ✔ | ✔ | ✔ |  |
| Github/QQ群/微信群交流提问 | ✔ | ✔ | ✔ | ✔ | 回复时长不确定 |
| 设计文档 |  | ✔ | ✔ | ✔ | 在线浏览 |
| 接口文档（后台+前台） |  | ✔ | ✔ | ✔ | 在线浏览/Swagger 格式导出/Markdown |
| 一对一问题咨询 |  |  | ✔ | ✔ | 回复及时，隐私更好 |
| 项目搭建部署（1次） |  |  |  | ✔ | 建议提供干净的服务器，可用内存至少1G。后续升级请自行操作，或另外付费。 |
| 私有化部署模型（ChatGLM3） |  |  |  | ✔ | 支持部署 ChatGLM3 模型在自己的 GPU 服务器，提供部署说明 |

> 2024-02-24 之前赞助的黄金级别，仍可享受 1 次免费项目搭建部署

#### 私有化部署模型

用户需自行准备好带 16G 及以上显存显卡的机器（支持多显卡），可以没有公网IP，可以是家里的电脑，但必须能联网（建议境外IP）。

Windows 需提前准备好：远程连接工具（向日葵、RustDesk 等）、 WSL2、Docker。

Linux 需提前准备好：SSH连接参数（请勿使用桌面系统远程）。

部署完全前，请保证远程连接或 SSH 通道畅通！

**支付：**

![赞助](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/pay.png)

**微信：**

![微信](https://cdn.jsdelivr.net/gh/imiphp/imi-ai@master/res/wechat.png)
