<?php

declare(strict_types=1);

namespace app\ApiServer\HttpController;

use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Server\View\Annotation\View;
use Imi\Server\WebSocket\Route\Annotation\WSConfig;

/**
 * 测试.
 */
#[
    Controller,
    View(renderType: 'html')
]
class HandShakeController extends HttpController
{
    /**
     * @return void
     */
    #[
        Action,
        Route(url: '/ws'),
        WSConfig(parserClass: \Imi\Server\DataParser\JsonObjectParser::class)
    ]
    public function ws(string $token = '')
    {
        // 握手处理，什么都不做，框架会帮你做好
    }
}
