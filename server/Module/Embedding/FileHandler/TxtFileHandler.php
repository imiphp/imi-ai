<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use app\Module\OpenAI\Util\Gpt3Tokenizer;
use Imi\Bean\Annotation\Bean;

/**
 * @Bean("TxtFileHandler")
 */
class TxtFileHandler implements IFileHandler
{
    public function parseSections(string $content, int $sectionSplitLength, string $sectionSeparator, bool $splitByTitle, string $model): \Generator
    {
        // 分隔符分割
        if ('' === $sectionSeparator)
        {
            $items = (array) $content;
        }
        else
        {
            $items = explode($sectionSeparator, $content);
        }
        foreach ($items as $splitItem)
        {
            $splitItem = trim($splitItem);
            // 长度
            foreach (Gpt3Tokenizer::encodeChunks($splitItem, $sectionSplitLength, $model) as $chunk)
            {
                $tokens = Gpt3Tokenizer::count($chunk, $model);
                yield ['', $chunk, $tokens];
            }
        }
    }
}
