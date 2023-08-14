<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt\Contract;

interface IPromptCrawler
{
    /**
     * @return \Iterator<\app\Module\Chat\Model\Prompt>
     */
    public function crawl(): \Iterator;
}
