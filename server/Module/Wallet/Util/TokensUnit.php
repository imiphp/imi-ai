<?php

declare(strict_types=1);

namespace app\Module\Wallet\Util;

use Imi\Util\Traits\TStaticClass;

class TokensUnit
{
    use TStaticClass;

    public const BYTE_UNITS = ['', '万', '亿'];

    public static function formatChinese(int $tokens, int $decimal = 3): string
    {
        $i = 0;
        while ($tokens >= 10000)
        {
            $tokens /= 10000;
            ++$i;
        }
        if ($decimal > 0)
        {
            $result = substr(number_format($tokens, $decimal + 1, '.', ''), 0, -1);
        }
        else
        {
            $result = (int) $tokens;
        }

        return $result . (self::BYTE_UNITS[$i] ?? '');
    }
}
