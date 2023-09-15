<?php

declare(strict_types=1);

namespace app\Util;

use app\Module\OpenAI\Model\Redis\ModelConfig;
use Imi\Util\Traits\TStaticClass;

class TokensUtil
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

    public static function calcDeductToken(?ModelConfig $modelConfig, int $inputTokens, int $outputTokens): array
    {
        // 按最大倍率计算返回
        return [
            (int) ceil($inputTokens * (float) ($modelConfig->inputTokenMultiple ?? 1)),
            (int) ceil($outputTokens * (float) ($modelConfig->outputTokenMultiple ?? 1)),
        ];
    }
}
