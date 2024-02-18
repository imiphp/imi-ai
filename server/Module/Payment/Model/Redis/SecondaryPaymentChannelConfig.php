<?php

declare(strict_types=1);

namespace app\Module\Payment\Model\Redis;

use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\JsonDecode;
use Imi\Model\BaseModel;

#[
    Entity(),
]
class SecondaryPaymentChannelConfig extends BaseModel
{
    #[
        Column(type: 'int'),
    ]
    protected ?SecondaryPaymentChannel $secondaryPaymentChannel = null;

    #[
        Column(),
    ]
    protected string $title = '';

    /**
     * 三级支付渠道配置.
     *
     * @var TertiaryPaymentChannelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: TertiaryPaymentChannelConfig::class, arrayWrap: true),
    ]
    protected array $tertiaryPaymentChannels = [];

    public function __init(array $data = []): void
    {
        parent::__init($data);
        foreach (TertiaryPaymentChannel::cases() as $case)
        {
            $tertiaryPaymentChannel = null;
            foreach ($this->tertiaryPaymentChannels as $item)
            {
                if ($item->getTertiaryPaymentChannel() === $case)
                {
                    $tertiaryPaymentChannel = $case;
                }
            }
            if (null === $tertiaryPaymentChannel)
            {
                $tertiaryPaymentChannel = new TertiaryPaymentChannelConfig();
                $tertiaryPaymentChannel->setTertiaryPaymentChannel($case);
                $this->tertiaryPaymentChannels[] = $tertiaryPaymentChannel;
            }
        }
    }

    public function getSecondaryPaymentChannel(): ?SecondaryPaymentChannel
    {
        return $this->secondaryPaymentChannel;
    }

    public function setSecondaryPaymentChannel(int|SecondaryPaymentChannel $secondaryPaymentChannel): self
    {
        $this->secondaryPaymentChannel = \is_int($secondaryPaymentChannel) ? SecondaryPaymentChannel::from($secondaryPaymentChannel) : $secondaryPaymentChannel;

        return $this;
    }

    public function getTertiaryPaymentChannels(): array
    {
        return $this->tertiaryPaymentChannels;
    }

    public function setTertiaryPaymentChannels(array $tertiaryPaymentChannels): self
    {
        $this->tertiaryPaymentChannels = [];
        foreach ($tertiaryPaymentChannels as $channel)
        {
            if (\is_array($channel))
            {
                $this->tertiaryPaymentChannels[] = new TertiaryPaymentChannelConfig($channel);
            }
            else
            {
                $this->tertiaryPaymentChannels[] = $channel;
            }
        }

        return $this;
    }

    public function getTitle(): string
    {
        return $this->getSecondaryPaymentChannel()?->getTitle() ?? '';
    }
}
