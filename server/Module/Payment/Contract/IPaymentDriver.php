<?php

declare(strict_types=1);

namespace app\Module\Payment\Contract;

use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Struct\PaymentCallbackResult;
use app\Module\Payment\Struct\RefundResult;
use Psr\Http\Message\ServerRequestInterface;

interface IPaymentDriver
{
    public function pay(array $paymentConfig, SecondaryPaymentChannel $secondaryPaymentChannel, TertiaryPaymentChannel $tertiaryPaymentChannel, string $tradeNo, string $title, int $amount, array $options = []): array;

    public function getPayApiData(array $result): array;

    /**
     * @param string $tradeNo  要退款的单号
     * @param string $refundNo 退款交易单号
     */
    public function refund(array $paymentConfig, ?SecondaryPaymentChannel $secondaryPaymentChannel, ?TertiaryPaymentChannel $tertiaryPaymentChannel, string $tradeNo, string $refundNo, int $amount, string $reason = '', array $options = []): RefundResult;

    public function payCallback(array $paymentConfig, ServerRequestInterface $request): PaymentCallbackResult;

    public function refundCallback(array $paymentConfig, ServerRequestInterface $request): RefundResult;
}
