<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\DTO;

use app\Module\Embedding\Model\EmbeddingFile;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * 训练的文件.
 */
#[
    Inherit,
    Serializables(mode: 'deny', fields: ['id', 'projectId', 'content']),
]
class EmbeddingFileInList extends EmbeddingFile
{
    protected static ?string $saltClass = parent::class;
}
