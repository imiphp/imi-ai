<?php

declare(strict_types=1);

namespace app\Module\Chat\Service;

use app\Module\Chat\Model\PromptCrawlerOrigin;

class PromptCrawlerOriginService
{
    public function getByTitle(string $class): ?PromptCrawlerOrigin
    {
        return PromptCrawlerOrigin::find(['class' => $class]);
    }

    public function create(string $class): PromptCrawlerOrigin
    {
        $origin = PromptCrawlerOrigin::newInstance();
        $origin->class = $class;
        $origin->insert();

        return $origin;
    }
}
