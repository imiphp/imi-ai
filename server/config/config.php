<?php

declare(strict_types=1);

use Imi\App;
use Imi\AppContexts;

use function Imi\env;

return [
    // 配置文件
    'configs'    => [
        'beans'  => __DIR__ . '/beans.php',
        'ai'     => __DIR__ . '/ai.php',
        'openai' => __DIR__ . '/openai.php',
    ],

    'debug'                  => env('APP_DEBUG', true),

    'ignoreNamespace'   => [
    ],

    'ignorePaths' => [
        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'bin',
        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Module/*/template',
    ],

    // Swoole 主服务器配置
    'mainServer'    => [
        'namespace'    => 'app\ApiServer',
        // @phpstan-ignore-next-line
        'type'         => Imi\Swoole\Server\Type::WEBSOCKET,
        'host'         => env('APP_HOST', '0.0.0.0'),
        'port'         => env('APP_PORT', 8080),
        'configs'      => [
            'worker_num'        => env('APP_WORKER_NUM', 1),
            'task_worker_num'   => env('APP_TASK_WORKER_NUM', 0),
        ],
    ],

    // Swoole 子服务器（端口监听）配置
    'subServers'        => [
        // 'SubServerName'   =>  [
        //     'namespace'    =>    'app\XXXServer',
        //     'type'        =>    Imi\Server\Type::HTTP,
        //     'host'        =>    '0.0.0.0',
        //     'port'        =>    13005,
        // ]
    ],

    // 连接池配置
    'pools'    => [
        'mysql'             => [
            // 同步池子
            'pool'        => [
                'class'        => \Imi\Swoole\Db\Pool\CoroutineDbPool::class,
                'config'       => [
                    'maxResources'              => 16,
                    'minResources'              => 0,
                    'checkStateWhenGetResource' => false,
                    'heartbeatInterval'         => 60,
                ],
            ],
            'resource'    => [
                'host'        => env('APP_MYSQL_HOST', '127.0.0.1'),
                'port'        => env('APP_MYSQL_PORT', 3306),
                'username'    => env('APP_MYSQL_USERNAME', 'root'),
                'password'    => env('APP_MYSQL_PASSWORD', 'root'),
                'database'    => env('APP_MYSQL_DATABASE', 'db_imi_ai'),
                'charset'     => 'utf8mb4',
                'options'     => [
                    \PDO::ATTR_STRINGIFY_FETCHES    => false,
                    \PDO::ATTR_EMULATE_PREPARES     => false,
                ],
                'initSqls'    => [
                    'set session transaction isolation level read committed',
                ],
            ],
        ],
        'pgsql'    => [
            'pool'    => [
                // @phpstan-ignore-next-line
                'class'        => \Imi\Swoole\Db\Pool\CoroutineDbPool::class,
                'config'       => [
                    'maxResources'              => 32,
                    'minResources'              => 0,
                    'checkStateWhenGetResource' => false,
                    'heartbeatInterval'         => 60,
                ],
            ],
            'resource'    => [
                'host'        => env('APP_PGSQL_HOST', '127.0.0.1'),
                'port'        => env('APP_PGSQL_PORT', 5432),
                'username'    => env('APP_PGSQL_USERNAME', 'root'),
                'password'    => env('APP_PGSQL_PASSWORD', 'root'),
                'database'    => env('APP_PGSQL_DATABASE', 'db_imi_ai'),
                'dbClass'     => \Imi\Pgsql\Db\Drivers\SwooleNew\Driver::class,
                // 'dbClass'     => \Imi\Pgsql\Db\Drivers\PdoPgsql\Driver::class,
            ],
        ],
        'redis'              => [
            'pool'        => [
                'class'        => \Imi\Swoole\Redis\Pool\CoroutineRedisPool::class,
                'config'       => [
                    'maxResources'              => 16,
                    'minResources'              => 0,
                    'checkStateWhenGetResource' => false,
                    'heartbeatInterval'         => 60,
                ],
            ],
            'resource'    => [
                'host'      => env('APP_REDIS_HOST', '127.0.0.1'),
                'port'      => env('APP_REDIS_PORT', 6379),
                'password'  => env('APP_REDIS_PASSWORD'),
                'serialize' => false,
                'options'   => [
                    \Redis::OPT_PREFIX => env('APP_REDIS_PREFIX', 'imi_ai:'),
                ],
            ],
        ],
    ],

    // 数据库配置
    'db'    => [
        // 数默认连接池名
        'defaultPool'    => 'mysql',
        'paginate'       => [
            'fields' => [
                'pageCount' => 'pageCount',
            ],
        ],
    ],

    'caches' => [
        'memory' => [
            'handlerClass' => \Imi\Cache\Handler\Memory::class,
            'option'       => [],
        ],
        'redisCache'        => [
            // 缓存驱动类
            'handlerClass'    => \Imi\Cache\Handler\Redis::class,
            // 驱动实例配置
            'option'          => [
                'poolName'              => 'redis',
                'prefix'                => 'cache:', // 缓存键前缀
                'formatHandlerClass'    => \Imi\Util\Format\Json::class, // 数据读写修改器
            ],
        ],
        'redisCacheSerialize'        => [
            // 缓存驱动类
            'handlerClass'    => \Imi\Cache\Handler\Redis::class,
            // 驱动实例配置
            'option'          => [
                'poolName'              => 'redis',
                'prefix'                => 'cache:', // 缓存键前缀
                'formatHandlerClass'    => \Imi\Util\Format\PhpSerialize::class, // 数据读写修改器
            ],
        ],
    ],

    'tools'  => [
        'generate/model'    => [
            'namespace' => [
                'app\Module\Chat\Model' => [
                    'tables'    => [
                        'tb_chat_session',
                        'tb_chat_message',
                        'tb_prompt',
                        'tb_prompt_category',
                        'tb_prompt_crawler_origin',
                    ],
                ],
                'app\Module\Embedding\Model' => [
                    'tables'    => [
                        'tb_embedding_project',
                        'tb_embedding_file',
                        'tb_embedding_section',
                        'tb_embedding_qa',
                        'tb_embedding_public_project',
                    ],
                ],
                'app\Module\Member\Model' => [
                    'tables'    => [
                        'tb_member',
                    ],
                ],
                'app\Module\Card\Model' => [
                    'tables'    => [
                        'tb_card',
                        'tb_card_type',
                        'tb_card_detail',
                        'tb_member_card_order',
                        'tb_member_card_refund_order',
                    ],
                ],
            ],
        ],
    ],

    // redis 配置
    'redis' => [
        // 数默认连接池名
        'defaultPool'   => 'redis',
    ],

    // 日志配置
    'logger' => [
        'async'    => true, // 是否启用异步日志，仅 Swoole 模式有效，可以有效提升大量日志记录时的接口响应速度
        'channels' => [
            'imi' => [
                'handlers' => [
                    [
                        'class'     => \Imi\Log\Handler\ConsoleHandler::class,
                        'formatter' => [
                            'class'     => \Imi\Log\Formatter\ConsoleLineFormatter::class,
                            'construct' => [
                                'format'                     => null,
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                    [
                        'class'     => \Monolog\Handler\RotatingFileHandler::class,
                        'construct' => [
                            'filename' => App::get(AppContexts::APP_PATH_PHYSICS) . '/.runtime/logs/log.log',
                        ],
                        'formatter' => [
                            'class'     => \Monolog\Formatter\LineFormatter::class,
                            'construct' => [
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
