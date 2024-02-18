<?php

declare(strict_types=1);

namespace app\Module\Payment\Model\Redis;

use app\Module\Payment\Enum\TertiaryPaymentChannel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\BaseModel;

#[
    Entity(),
]
class TertiaryPaymentChannelConfig extends BaseModel
{
    #[
        Column(),
    ]
    protected ?TertiaryPaymentChannel $tertiaryPaymentChannel = null;

    #[
        Column(),
    ]
    protected string $title = '';

    /**
     * 使用的支付渠道名称.
     *
     * 空字符串则不启用
     */
    #[
        Column(),
    ]
    protected string $paymentChannelName = '';

    public function getTertiaryPaymentChannel(): ?TertiaryPaymentChannel
    {
        return $this->tertiaryPaymentChannel;
    }

    public function setTertiaryPaymentChannel(int|TertiaryPaymentChannel $tertiaryPaymentChannel): self
    {
        $this->tertiaryPaymentChannel = \is_int($tertiaryPaymentChannel) ? TertiaryPaymentChannel::from($tertiaryPaymentChannel) : $tertiaryPaymentChannel;

        return $this;
    }

    public function getPaymentChannelName(): string
    {
        return $this->paymentChannelName;
    }

    public function setPaymentChannelName(string $paymentChannelName): self
    {
        $this->paymentChannelName = $paymentChannelName;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->getTertiaryPaymentChannel()?->getTitle() ?? '';
    }
}
