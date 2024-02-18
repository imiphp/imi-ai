<?php

declare(strict_types=1);

namespace app\Module\Payment\Service;

use app\Exception\NotFoundException;
use app\Module\Payment\Annotation\PaymentChannel;
use app\Module\Payment\Annotation\SubPaymentChannel;
use app\Module\Payment\Enum\SecondaryPaymentChannel;
use app\Module\Payment\Enum\TertiaryPaymentChannel;
use app\Module\Payment\Model\PaymentChannel as PaymentChannelModel;
use app\Module\Payment\Model\Redis\PaymentChannelConfig;
use app\Module\Payment\Model\Redis\PaymentConfig;
use Imi\Bean\Annotation\AnnotationManager;
use Imi\Cache\Annotation\Cacheable;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Query\Lock\MysqlLock;
use Imi\Log\Log;

class PaymentChannelService
{
    /**
     * @var array<string, array{class: string, annotation: PaymentChannel, subPaymentChannels: SubPaymentChannel[]}>
     */
    private array $paymentChannels = [];

    private array $isSupportChannelCachedMap = [];

    public function __construct()
    {
        foreach (AnnotationManager::getAnnotationPoints(PaymentChannel::class, 'class') as $point)
        {
            /** @var PaymentChannel $paymentChannelAnnotation */
            $paymentChannelAnnotation = $point->getAnnotation();
            if (isset($this->paymentChannels[$paymentChannelAnnotation->name]))
            {
                Log::warning(sprintf('PaymentChannel %s 重复存在', $paymentChannelAnnotation->name));
            }
            /** @var SubPaymentChannel[] $subPaymentChannels */
            $subPaymentChannels = AnnotationManager::getClassAnnotations($point->getClass(), SubPaymentChannel::class);
            $this->paymentChannels[$paymentChannelAnnotation->name] = [
                'class'              => $point->getClass(),
                'annotation'         => $paymentChannelAnnotation,
                'subPaymentChannels' => $subPaymentChannels,
            ];
        }
    }

    public function create(string $channelName): PaymentChannelModel
    {
        $record = PaymentChannelModel::newInstance();
        $record->setName($channelName);
        $record->save();

        return $record;
    }

    /**
     * @return array<string, array{class: string, annotation: PaymentChannel}>
     */
    public function getPaymentChannels(): array
    {
        return $this->paymentChannels;
    }

    /**
     * @return array{class: string, annotation: PaymentChannel, subPaymentChannels: SubPaymentChannel[]}
     */
    public function getPaymentChannelOptions(string $name): array
    {
        if (!isset($this->paymentChannels[$name]))
        {
            throw new \RuntimeException(sprintf('PaymentChannel %s does not exists', $name));
        }

        return $this->paymentChannels[$name];
    }

    public function getPaymentChannelConfig(string $name): PaymentChannelConfig
    {
        if (!isset($this->paymentChannels[$name]))
        {
            throw new \RuntimeException(sprintf('PaymentChannel %s does not exists', $name));
        }

        return PaymentConfig::__getConfig()->getChannelWithCheck($name);
    }

    #[Cacheable(name: 'memory', key: 'payment:channelId:{channelName}')]
    public function getChannelId(string $channelName): int
    {
        return $this->getChannelIdWithLock($channelName);
    }

    #[
        Transaction()
    ]
    public function getChannelIdWithLock(string $channelName): int
    {
        $record = PaymentChannelModel::query()->lock(MysqlLock::FOR_UPDATE)
                                              ->where('name', '=', $channelName)
                                              ->find();
        if (!$record)
        {
            return $this->create($channelName)->getId();
        }

        return $record->getId();
    }

    #[Cacheable(name: 'memory', key: 'payment:channelName:{channelName}')]
    public function getChannelName(int $channelId): string
    {
        $record = PaymentChannelModel::find($channelId);
        if (!$record)
        {
            throw new NotFoundException(sprintf('支付渠道 id %d 不存在', $channelId));
        }

        return $record->getName();
    }

    public function isSupportChannel(string $channelName, ?SecondaryPaymentChannel $secondaryPaymentChannel = null, ?TertiaryPaymentChannel $tertiaryPaymentChannel = null): bool
    {
        if (isset($this->isSupportChannelCachedMap[$channelName][$secondaryPaymentChannel][$tertiaryPaymentChannel]))
        {
            return $this->isSupportChannelCachedMap[$channelName][$secondaryPaymentChannel][$tertiaryPaymentChannel];
        }

        return $this->isSupportChannelCachedMap[$channelName][$secondaryPaymentChannel][$tertiaryPaymentChannel] = $this->__isSupportChannel($channelName, $secondaryPaymentChannel, $tertiaryPaymentChannel);
    }

    private function __isSupportChannel(string $channelName, ?SecondaryPaymentChannel $secondaryPaymentChannel = null, ?TertiaryPaymentChannel $tertiaryPaymentChannel = null): bool
    {
        try
        {
            $options = $this->getPaymentChannelOptions($channelName);
        }
        catch (\RuntimeException)
        {
            return false;
        }
        if ($secondaryPaymentChannel)
        {
            $subPaymentChannels = $options['subPaymentChannels'];
            foreach ($subPaymentChannels as $subPaymentChannel)
            {
                if ($subPaymentChannel->secondary === $secondaryPaymentChannel)
                {
                    if ($tertiaryPaymentChannel)
                    {
                        return \in_array($tertiaryPaymentChannel, $subPaymentChannel->tertiaries, true);
                    }

                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
