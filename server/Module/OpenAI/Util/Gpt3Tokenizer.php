<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Util;

use Yethee\Tiktoken\Encoder;
use Yethee\Tiktoken\EncoderProvider;

class Gpt3Tokenizer
{
    private static ?EncoderProvider $provider = null;

    public static function getInstance(string $model): Encoder
    {
        if ('' === $model)
        {
            throw new \InvalidArgumentException('Model cannot be empty');
        }
        if (null === self::$provider)
        {
            self::$provider = new EncoderProvider();
        }

        return self::$provider->getForModel($model);
    }

    public static function encode(string $text, string $model): array
    {
        return self::getInstance($model)->encode($text);
    }

    public static function encodeChunks(string $text, int $maxTokenPerChunk, string $model): array
    {
        return self::getInstance($model)->encodeChunks($text, $maxTokenPerChunk);
    }

    public static function decode(array $tokens, string $model): string
    {
        return self::getInstance($model)->decode($tokens);
    }

    public static function count(string $text, string $model): int
    {
        return \count(self::encode($text, $model));
    }
}
