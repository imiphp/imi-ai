<?php

declare(strict_types=1);

use Imi\App;

return [
    'configs'    => [
    ],
    'beans'    => [
        'HttpDispatcher'    => [
            'middlewares'    => [
                ...(App::isDebug() ? ['OptionsMiddleware'] : []),
                \Imi\Swoole\Server\WebSocket\Middleware\HandShakeMiddleware::class,
                \Imi\Server\Http\Middleware\RouteMiddleware::class,
            ],
        ],
        'HttpErrorHandler'    => [
            'handler'    => \app\ApiServer\ErrorHandler\HttpErrorHandler::class,
        ],
        'WebSocketDispatcher'    => [
            'middlewares'    => [
                \Imi\Server\WebSocket\Middleware\RouteMiddleware::class,
            ],
        ],
        'ConnectionContextStore'   => [
            'handlerClass'  => \Imi\Server\ConnectionContext\StoreHandler\Local::class,
        ],
        'OptionsMiddleware' => [
            // 设置允许的 Origin，为 null 时允许所有，为数组时允许多个
            'allowOrigin'       => null,
            // 允许的请求头
            'allowHeaders'      => 'Authorization, Content-Type, Accept, Origin, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, X-Id, X-Token, Cookie',
            // 允许的跨域请求头
            'exposeHeaders'     => 'Authorization, Content-Type, Accept, Origin, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With, X-Id, X-Token, Cookie',
            // 允许的请求方法
            'allowMethods'      => 'GET, POST, PATCH, PUT, DELETE',
            // 是否允许跨域 Cookie
            'allowCredentials'  => 'true',
            // 当请求为 OPTIONS 时，是否中止后续中间件和路由逻辑，一般建议设为 true
            'optionsBreak'      => true,
        ],
    ],
];
