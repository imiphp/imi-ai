<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Admin;

use app\Module\Chat\Model\PromptCrawlerOrigin;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializable;
use Imi\Model\Annotation\Serializables;

/**
 * 提示语.
 */
#[
    Inherit,
    Serializables(mode: 'deny', fields: ['categorys'])
]
class Prompt extends \app\Module\Chat\Model\Prompt
{
    protected static ?string $saltClass = parent::class;

    #[
        OneToOne(model: PromptCrawlerOrigin::class, with: true),
        JoinFrom(field: 'crawler_origin_id'),
        JoinTo(field: 'id'),
        Serializable(allow: false)
    ]
    protected ?PromptCrawlerOrigin $crawlerOrigin = null;

    public function getCrawlerOrigin(): ?PromptCrawlerOrigin
    {
        return $this->crawlerOrigin;
    }

    public function setCrawlerOrigin(?PromptCrawlerOrigin $crawlerOrigin): self
    {
        $this->crawlerOrigin = $crawlerOrigin;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?string $crawlerOriginClass = null;

    public function getCrawlerOriginClass(): ?string
    {
        if ($this->crawlerOrigin)
        {
            return $this->crawlerOrigin->class;
        }

        return $this->crawlerOriginClass;
    }
}
