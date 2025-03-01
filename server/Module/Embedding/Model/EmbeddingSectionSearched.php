<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model;

use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;
use Imi\Model\Annotation\Sql;

/**
 * 内容部分.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['vector'])]
class EmbeddingSectionSearched extends EmbeddingSection
{
    #[
        Column(virtual: true),
        Sql(sql: 'cosine_distance("vector", :keyword)')
    ]
    protected ?float $distance = null;

    public function getDistance(): ?float
    {
        return $this->distance;
    }

    public function setDistance(float|string|null $distance): self
    {
        $this->distance = null === $distance ? null : (float) $distance;

        return $this;
    }
}
