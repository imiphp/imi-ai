<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use app\Module\Chat\Util\Gpt3Tokenizer;
use Imi\Bean\Annotation\Bean;

/**
 * @Bean("TxtFileHandler")
 */
class TxtFileHandler implements IFileHandler
{
    public function parseSections(string $content, int $sectionSplitLength, string $sectionSeparator, bool $splitByTitle): \Generator
    {
        $tokenizer = Gpt3Tokenizer::getInstance();
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
            foreach ($tokenizer->chunk($splitItem, $sectionSplitLength) as $chunk)
            {
                $tokens = $tokenizer->count($chunk);
                yield [$chunk, $tokens];
            }
        }
    }
}
