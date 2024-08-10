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
        return mb_strlen($string);
    }
}
