<?php

declare(strict_types=1);

namespace app\Module\Payment\Model\Redis;

use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Service\PaymentChannelService;
use Imi\App;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\BaseModel;

#[
    Entity(),
]
class PaymentChannelConfig extends BaseModel
{
    /**
     * 渠道名称.
     */
    #[Column]
    protected string $name = '';

    /**
     * 是否启用.
     */
    #[Column]
    protected bool $enable = false;

    /**
     * 支付渠道配置.
     */
    #[Column]
    protected ?array $config = null;

    /**
     * 渠道标题.
     */
    #[Column]
    protected string $title = '';

    /**
     * 子渠道.
     */
    #[Column]
    protected array $subChannels = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEnable(): bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): self
    {
        $this->enable = $enable;

        return $this;
    }

    public function &getConfig(): ?array
    {
        return $this->config;
    }

    public function setConfig(?array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubChannels(): array
    {
        $paymentChannelService = App::getBean(PaymentChannelService::class);
        try
        {
            $options = $paymentChannelService->getPaymentChannelOptions($this->name);
        }
        catch (\RuntimeException)
        {
            return [];
        }
        $result = [];
        foreach ($options['subPaymentChannels'] as $subPaymentChannel)
        {
            $result[] = [
                'secondary'       => $subPaymentChannel->secondary,
                'secondaryTitle'  => $subPaymentChannel->secondary->getTitle(),
                'tertiaries'      => array_map(static fn (TertiaryPaymentChannel $tertiaryPaymentChannel) => [
                    'tertiary'      => $tertiaryPaymentChannel,
                    'tertiaryTitle' => $tertiaryPaymentChannel->getTitle(),
                ], $subPaymentChannel->tertiaries),
            ];
        }

        return $result;
    }
}
