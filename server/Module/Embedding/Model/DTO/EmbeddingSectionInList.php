<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\DTO;

use app\Module\Embedding\Model\EmbeddingSection;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * 训练内容段落.
 */
#[
    Inherit,
    Serializables(mode: 'deny', fields: ['id', 'projectId', 'fileId', 'vector']),
]
class EmbeddingSectionInList extends EmbeddingSection
{
    protected static ?string $saltClass = parent::class;
}
