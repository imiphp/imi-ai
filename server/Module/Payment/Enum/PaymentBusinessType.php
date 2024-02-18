<?php

declare(strict_types=1);

namespace app\Module\Payment\Enum;

use app\Module\Config\Annotation\PublicEnum;
use app\Module\Config\Contract\IEnum;

#[PublicEnum(name: 'PaymentBusinessType')]
enum PaymentBusinessType: int implements IEnum
{
    /**
     * 购买卡包.
     */
    case Card = 1;

    public function getTitle(): string
    {
        return match ($this)
        {
            self::Card => '购买卡包',
        };
    }
}
