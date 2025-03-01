<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use app\Module\OpenAI\Util\Gpt3Tokenizer;

abstract class BaseFileHandler implements IFileHandler
{
    public function __construct(protected string $fileName)
    {
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    protected function chunk(string $content, int $sectionSplitLength, string $model): \Generator
    {
        try
        {
            // 优先尝试用 OpenAI 方式分割
            foreach (Gpt3Tokenizer::chunk($content, $sectionSplitLength, $model) as $chunk)
            {
                yield $chunk;
            }
        }
        catch (\Throwable)
        {
            // 使用字符串分割
            $content = trim($content);
            $contentLength = mb_strlen($content);
            $start = 0;
            while ($start < $contentLength)
            {
                $end = $start + $sectionSplitLength;
                if ($end >= $contentLength)
                {
                    $end = $contentLength;
                }
                $chunk = mb_substr($content, $start, $end - $start);
                yield $chunk;
                $start = $end;
            }
        }
    }

    protected function calcTokens(string $content, string $model): int
    {
        try
        {
            // 优先尝试用 OpenAI 方式计算
            return Gpt3Tokenizer::count($content, $model);
        }
        catch (\Throwable)
        {
            // OpenAI 方式计算失败，使用字符串长度计算
            return mb_strlen($content);
        }
    }
}
