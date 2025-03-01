<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\LikeOpenAI;

use app\Module\OpenAI\Client\Annotation\OpenAIClient;

/**
 * 类 OpenAI 客户端，计算 Tokens 用字符串长度.
 */
#[
    OpenAIClient(title: 'Like OpenAI'),
]
class Client extends \app\Module\OpenAI\Client\OpenAI\Client
{
    public function calcTokens(string $string, string $model): int
    {
        try
        {
            // 优先尝试用 OpenAI 方式计算
            return parent::calcTokens($string, $model);
        }
        catch (\Throwable)
        {
            // OpenAI 方式计算失败，使用字符串长度计算
            return mb_strlen($string);
        }
    }
}
