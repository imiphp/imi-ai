<?php

declare(strict_types=1);

namespace app\Module\Payment\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Service\PaymentChannelService;
use Imi\App;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\JsonDecode;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\Annotation\Serializables;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:payment', storage: 'hash_object'),
    ConfigModel(title: '支付渠道设置'),
    Serializables(mode: 'allow', fields: ['secondaryPaymentChannels']),
]
class PaymentConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 支付渠道配置.
     *
     * @var PaymentChannelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: PaymentChannelConfig::class, arrayWrap: true),
    ]
    protected array $channels = [];

    /**
     * 二级支付渠道配置.
     *
     * @var SecondaryPaymentChannelConfig[]
     */
    #[
        Column(type: 'json'),
        JsonDecode(wrap: SecondaryPaymentChannelConfig::class, arrayWrap: true),
    ]
    protected array $secondaryPaymentChannels = [];

    /**
     * @var array<string, PaymentChannelConfig>
     */
    protected array $mappedChannels = [];

    public function __init(array $data = []): void
    {
        parent::__init($data);
        $paymentChannelService = App::getBean(PaymentChannelService::class);
        foreach ($paymentChannelService->getPaymentChannels() as $name => $item)
        {
            if (!isset($this->mappedChannels[$name]))
            {
                $this->channels[] = $this->mappedChannels[$name] = $config = new PaymentChannelConfig();
                $config->setName($name);
                $config->setTitle($item['annotation']->title);
            }
        }
        foreach (SecondaryPaymentChannel::cases() as $case)
        {
            $secondaryPaymentChannel = null;
            foreach ($this->secondaryPaymentChannels as $item)
            {
                if ($item->getSecondaryPaymentChannel() === $case)
                {
                    $secondaryPaymentChannel = $case;
                }
            }
            if (null === $secondaryPaymentChannel)
            {
                $secondaryPaymentChannel = new SecondaryPaymentChannelConfig();
                $secondaryPaymentChannel->setSecondaryPaymentChannel($case);
                $this->secondaryPaymentChannels[] = $secondaryPaymentChannel;
            }
        }
    }

    /**
     * @return PaymentChannelConfig[]
     */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /**
     * @param PaymentChannelConfig[]|array $channels
     */
    public function setChannels(array $channels): self
    {
        $this->channels = [];
        $mappedChannels = [];
        foreach ($channels as $channel)
        {
            if (\is_array($channel))
            {
                $this->channels[] = $mappedChannels[$channel['name']] = new PaymentChannelConfig($channel);
            }
            else
            {
                $this->channels[] = $mappedChannels[$channel->getName()] = $channel;
            }
        }
        $this->mappedChannels = $mappedChannels;

        return $this;
    }

    public function getSecondaryPaymentChannels(): array
    {
        return $this->secondaryPaymentChannels;
    }

    public function setSecondaryPaymentChannels(array $secondaryPaymentChannels): self
    {
        $this->secondaryPaymentChannels = [];
        foreach ($secondaryPaymentChannels as $channel)
        {
            if (\is_array($channel))
            {
                $this->secondaryPaymentChannels[] = new SecondaryPaymentChannelConfig($channel);
            }
            else
            {
                $this->secondaryPaymentChannels[] = $channel;
            }
        }

        return $this;
    }

    /**
     * 根据名称获取支付渠道.
     */
    public function getChannel(string $name): ?PaymentChannelConfig
    {
        return $this->mappedChannels[$name] ?? null;
    }

    /**
     * 根据名称获取支付渠道，并且做检测.
     */
    public function getChannelWithCheck(string $name): PaymentChannelConfig
    {
        $channelConfig = $this->getChannel($name);
        if (!$channelConfig)
        {
            throw new \RuntimeException(sprintf('PaymentChannel %s does not exists', $name));
        }
        if (!$channelConfig->getEnable())
        {
            throw new \RuntimeException(sprintf('PaymentChannel %s disabled', $name));
        }

        return $channelConfig;
    }

    /**
     * 将当前模型转为数组.
     *
     * 包括属性的值也会被转为数组
     *
     * @param bool $filter 过滤隐藏属性
     */
    public function convertToArray(bool $filter = true): array
    {
        if ($filter)
        {
            $data = $this->toArray();
        }
        else
        {
            $data = iterator_to_array($this);
            /** @var PaymentChannelConfig $channel */
            foreach ($data['channels'] as &$channel)
            {
                $channel = $channel->convertToArray($filter);
            }
        }

        return json_decode(json_encode($data, \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR);
    }
}
