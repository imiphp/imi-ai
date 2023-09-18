<?php

declare(strict_types=1);

namespace app\Util;

use Imi\Util\Traits\TStaticClass;

class RateLimit
{
    use TStaticClass;

    public static function getUnitHumanString(string $unit): string
    {
        return match ($unit)
        {
            'microsecond' => '微秒',
            'millisecond' => '毫秒',
            'second'      => '秒',
            'minute'      => '分钟',
            'hour'        => '小时',
            'day'         => '天',
            'week'        => '周',
            'month'       => '月',
            'year'        => '年',
            default       => $unit,
        };
    }
}
