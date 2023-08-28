<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Admin;

use app\Module\Embedding\Model\DTO\EmbeddingSectionInList;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * 文件训练项目.
 */
#[
    Inherit,
    Serializables(mode: 'deny', fields: ['vector']),
]
class EmbeddingSectionInListAdmin extends EmbeddingSectionInList
{
}
