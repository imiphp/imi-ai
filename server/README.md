# 说明

这是一个 imi http 项目开发骨架项目，你可以基于这个项目来开发你的项目。

imi 框架：<https://www.imiphp.com>

imi 文档：<https://doc.imiphp.com>

## 安装

创建项目：`composer create-project imiphp/project-http:~2.1.0`

如果你希望在 php-fpm、Workerman 运行 imi：`已内置`

如果你希望在 Swoole 运行 imi：`composer require imiphp/imi-swoole:~2.1.0`

如果你希望在 RoadRunner 运行 imi：`composer require imiphp/imi-roadrunner:~2.1.0`

> RoadRunner 二进制文件请自行下载！

## 配置

### 项目命名空间

默认是 `app`，可以在 `composer.json` 中修改：

* `autoload.psr-4.app`

* `imi.namespace`

然后替换代码中的命名空间即可。

### 运行配置

项目配置目录：`config`

HTTP 服务器配置目录：`ApiServer/config`

## 启动服务

**Swoole：**`vendor/bin/imi-swoole swoole/start` （强烈推荐）

**Workerman：**`vendor/bin/imi-workerman workerman/start` （推荐）

**RoadRunner：**`vendor/bin/imi-cli rr/start` （尝鲜）

> 切换环境运行前建议删除运行时文件目录：`rm -rf .runtime/*runtime`

**PHP-FPM：**`vendor/bin/imi-cli fpm/start`（不推荐）

建议在 FPM 模式下生成缓存：`vendor/bin/imi-cli imi/buildRuntime --app-namespace "app" --runtimeMode=Fpm`

## 目录

`.runtime` 是运行时文件读写目录，需要有可写权限

`public` 是 php-fpm 站点根目录，不用可删除

`rr` 是 RoadRunner 模式目录，不用可删除

## 生产环境

**关闭热更新：**`config/beans.php` 中 `hotUpdate.status` 设为 `false`

生产环境建议只保留一个容器，可以提升性能，imi 官方推荐使用 **Swoole**！

**移除 `imi-fpm`：**`composer remove imi-fpm && rm -rf public`

**移除 `imi-workerman`：**`composer remove imi-workerman`

**移除 `imi-roadrunner`：**`composer remove imi-roadrunner && rm -rf rr && rm -f .rr.yaml`

**移除 `imi-swoole`：**`composer remove imi-swoole`（不推荐）

## 代码质量

### 格式化代码

内置 `php-cs-fixer`，统一代码风格。

配置文件 `.php-cs-fixer.php`，可根据自己实际需要进行配置，文档：<https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/master/doc/config.rst>

**格式化项目：** `./vendor/bin/php-cs-fixer fix`

**格式化指定文件：** `./vendor/bin/php-cs-fixer fix test.php`

### 代码静态分析

内置 `phpstan`，可规范代码，排查出一些隐藏问题。

配置文件 `phpstan.neon`，可根据自己实际需要进行配置，文档：<https://phpstan.org/config-reference>

**分析项目：** `./vendor/bin/phpstan`

**分析指定文件：** `./vendor/bin/phpstan test.php`

### 测试用例

内置 `phpunit`，可以实现自动化测试。

**文档：**<https://phpunit.readthedocs.io/en/9.5/>

**测试用例 demo：**`tests/Module/Test/TestServiceTest.php`

**运行测试用例：**`composer test`
