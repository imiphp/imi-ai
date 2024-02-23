<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\Contract;

use app\Module\OpenAI\Model\Redis\Api;

interface IClient
{
    public function __construct(Api $api);

    public function getApi(): Api;

    public function chat(array $params, ?int &$inputTokens = null, ?int &$outputTokens = null): \Iterator;

    public function embedding(array $params): array;

    public function calcTokens(string $string, string $model): int;
}
