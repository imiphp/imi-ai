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

    /**
     * @param ModelConfig[] $modelConfigs
     */
    public static function calcDeductToken(string $model, int $inputTokens, int $outputTokens, array $modelConfigs): array
    {
        $modelConfig = null;
        foreach ($modelConfigs as $item)
        {
            if ($item->model === $model)
            {
                $modelConfig = $item;
                break;
            }
        }

        /** @var ModelConfig|null $modelConfig */
        if (!$modelConfig || !$modelConfig->enable)
        {
            // 没有配置，直接返回
            return [$inputTokens, $outputTokens];
        }

        // 按最大倍率计算返回
        return [
            (int) ceil($inputTokens * (float) $modelConfig->inputTokenMultiple),
            (int) ceil($outputTokens * (float) $modelConfig->outputTokenMultiple),
        ];
    }
}
