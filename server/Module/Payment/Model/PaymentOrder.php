<?php

declare(strict_types=1);

namespace app\Module\Payment\Model;

use app\Module\Member\Model\Traits\TMemberInfo;
use app\Module\Payment\Enum\OrderType;
use app\Module\Payment\Enum\PaymentBusinessType;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Model\Base\PaymentOrderBase;
use app\Module\Payment\Service\PaymentChannelService;
use Imi\App;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializable;

/**
 * 支付订单.
 *
 * @Inherit
 */
class PaymentOrder extends PaymentOrderBase
{
    use TMemberInfo;

    /**
     * 类型标题.
     */
    #[Column(virtual: true)]
    protected ?string $typeTitle = null;

    public function getTypeTitle(): ?string
    {
        return OrderType::tryFrom($this->type)?->getTitle() ?? '';
    }

    /**
     * 业务类型标题.
     */
    #[Column(virtual: true)]
    protected ?string $businessTypeTitle = null;

    public function getBusinessTypeTitle(): ?string
    {
        return PaymentBusinessType::tryFrom($this->type)?->getTitle() ?? '';
    }

    /**
     * 支付渠道关联.
     */
    #[
        OneToOne(model: PaymentChannel::class, with: true),
        JoinFrom(field: 'channel_id'),
        JoinTo(field: 'id'),
        Serializable(allow: false)
    ]
    protected ?PaymentChannel $paymentChannel = null;

    public function getPaymentChannel(): ?PaymentChannel
    {
        return $this->paymentChannel;
    }

    public function setPaymentChannel(?PaymentChannel $paymentChannel): self
    {
        $this->paymentChannel = $paymentChannel;

        return $this;
    }

    /**
     * 支付渠道标题.
     */
    #[Column(virtual: true)]
    protected ?string $channelTitle = null;

    public function getChannelTitle(): ?string
    {
        if (null !== $this->channelTitle)
        {
            return $this->channelTitle;
        }
        $channelName = $this->paymentChannel?->getName();
        if (null === $channelName)
        {
            return $this->channelTitle = '';
        }
        $paymentChannelService = App::getBean(PaymentChannelService::class);
        try
        {
            $options = $paymentChannelService->getPaymentChannelOptions($channelName);
        }
        catch (\Throwable)
        {
            return $this->channelTitle = $channelName;
        }

        return $this->channelTitle = $options['annotation']->title;
    }

    /**
     * 二级支付渠道标题.
     */
    #[Column(virtual: true)]
    protected ?string $secondaryChannelTitle = null;

    public function getSecondaryChannelTitle(): ?string
    {
        return $this->secondaryChannelTitle ??= SecondaryPaymentChannel::tryFrom($this->secondaryChannelId)?->getTitle() ?? ((string) $this->secondaryChannelId);
    }

    /**
     * 三级支付渠道标题.
     */
    #[Column(virtual: true)]
    protected ?string $tertiaryChannelTitle = null;

    public function getTertiaryChannelTitle(): ?string
    {
        return $this->tertiaryChannelTitle ??= TertiaryPaymentChannel::tryFrom($this->tertiaryChannelId)?->getTitle() ?? ((string) $this->tertiaryChannelId);
    }
}
