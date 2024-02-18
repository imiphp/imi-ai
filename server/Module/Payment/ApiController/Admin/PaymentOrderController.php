<?php

declare(strict_types=1);

namespace app\Module\Payment\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Payment\Enum\OrderType;
use app\Module\Payment\Enum\PaymentBusinessType;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Service\PaymentService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/admin/paymentOrder/')]
class PaymentOrderController extends HttpController
{
    #[Inject()]
    protected PaymentService $paymentService;

    #[
        Action,
        // AdminLoginRequired()
    ]
    public function list(string $search = '', PaymentBusinessType|int $businessType = 0, int $memberId = 0, OrderType|int $type = 0, string|int $channel = 0, SecondaryPaymentChannel|int $secondaryChannelId = 0, TertiaryPaymentChannel|int $tertiaryChannelId = 0, int $beginTime = 0, int $endTime = 0, int $page = 1, int $limit = 15): array
    {
        return $this->paymentService->list($search, $businessType, $memberId, $type, $channel, $secondaryChannelId, $tertiaryChannelId, $beginTime, $endTime, $page, $limit);
    }
}
