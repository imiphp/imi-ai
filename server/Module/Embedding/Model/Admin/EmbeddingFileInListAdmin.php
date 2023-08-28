<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Admin;

use app\Module\Embedding\Model\DTO\EmbeddingFileInList;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * 训练的文件.
 */
#[
    Inherit,
    Serializables(mode: 'deny', fields: ['content']),
]
class EmbeddingFileInListAdmin extends EmbeddingFileInList
{
}
