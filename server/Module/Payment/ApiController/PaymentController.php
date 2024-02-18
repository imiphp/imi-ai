<?php

declare(strict_types=1);

namespace app\Module\Payment\ApiController;

use app\Module\Payment\Service\PaymentResultCacheService;
use Imi\Aop\Annotation\Inject;
use Imi\Bean\Annotation\Bean;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[
    Bean(recursion: false),
    Controller(prefix: '/payment/')
]
class PaymentController extends HttpController
{
    #[Inject]
    protected PaymentResultCacheService $paymentResultCacheService;

    #[
        Action()
    ]
    public function result(string $tradeNo): array
    {
        return [
            'data' => $this->paymentResultCacheService->getResult($tradeNo),
        ];
    }
}
