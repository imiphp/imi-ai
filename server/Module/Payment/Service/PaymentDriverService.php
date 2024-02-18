<?php

declare(strict_types=1);

namespace app\Module\Payment\Service;

use app\Module\Payment\Contract\IPaymentDriver;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Struct\PaymentCallbackResult;
use app\Module\Payment\Struct\RefundResult;
use Imi\Aop\Annotation\Inject;
use Imi\App;
use Psr\Http\Message\ServerRequestInterface;

class PaymentDriverService
{
    #[Inject]
    protected PaymentChannelService $paymentChannelService;

    public function pay(string $channelName, SecondaryPaymentChannel $secondaryPaymentChannel, TertiaryPaymentChannel $tertiaryPaymentChannel, string $tradeNo, string $title, int $amount, array $options = []): array
    {
        return $this->getPaymentInstance($channelName, $channelPaymentConfig)->pay($channelPaymentConfig, $secondaryPaymentChannel, $tertiaryPaymentChannel, $tradeNo, $title, $amount, $options);
    }

    public function getPayApiData(string $channelName, array $result): array
    {
        return $this->getPaymentInstance($channelName)->getPayApiData($result);
    }

    public function refund(string $channelName, ?SecondaryPaymentChannel $secondaryPaymentChannel, ?TertiaryPaymentChannel $tertiaryPaymentChannel, string $tradeNo, string $refundNo, int $amount, string $reason = '', array $options = []): RefundResult
    {
        return $this->getPaymentInstance($channelName, $channelPaymentConfig)->refund($channelPaymentConfig, $secondaryPaymentChannel, $tertiaryPaymentChannel, $tradeNo, $refundNo, $amount, $reason, $options);
    }

    public function payCallback(string $channelName, ServerRequestInterface $request): PaymentCallbackResult
    {
        return $this->getPaymentInstance($channelName, $channelPaymentConfig)->payCallback($channelPaymentConfig, $request);
    }

    public function refundCallback(string $channelName, ServerRequestInterface $request): RefundResult
    {
        return $this->getPaymentInstance($channelName, $channelPaymentConfig)->refundCallback($channelPaymentConfig, $request);
    }

    public function getPaymentInstance(string $channelName, ?array &$channelPaymentConfig = null): IPaymentDriver
    {
        $channelConfig = $this->paymentChannelService->getPaymentChannelConfig($channelName);
        $channelPaymentConfig = $channelConfig->getConfig();

        return App::getBean($this->paymentChannelService->getPaymentChannelOptions($channelName)['class']);
    }
}
