<?php

declare(strict_types=1);

namespace app\Module\Embedding\FileHandler;

use Imi\Bean\Annotation\Bean;

/**
 * @Bean("TxtFileHandler")
 */
class TxtFileHandler extends BaseFileHandler
{
    protected ?string $content = null;

    public function getContent(): string
    {
        if (null === $this->content)
        {
            return $this->content = file_get_contents($this->getFileName());
        }

        return $this->content;
    }

    public function parseSections(int $sectionSplitLength, string $sectionSeparator, bool $splitByTitle, string $model): \Generator
    {
        // 分隔符分割
        if ('' === $sectionSeparator)
        {
            $items = (array) $this->content;
        }
        else
        {
            $items = explode($sectionSeparator, $this->content);
        }
        foreach ($items as $splitItem)
        {
            $splitItem = trim($splitItem);
            foreach ($this->chunk($splitItem, $sectionSplitLength, $model) as $chunk)
            {
                yield ['', $chunk, $this->calcTokens($chunk, $model)];
            }
        }
    }
}
