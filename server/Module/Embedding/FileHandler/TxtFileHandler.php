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
    public function parseSections(string $content, int $maxSectionTokens): \Generator
    {
        $tokenizer = Gpt3Tokenizer::getInstance();
        foreach ($tokenizer->chunk($content, $maxSectionTokens) as $chunk)
        {
            $tokens = $tokenizer->count($chunk);
            yield [$chunk, $tokens];
        }
    }
}
