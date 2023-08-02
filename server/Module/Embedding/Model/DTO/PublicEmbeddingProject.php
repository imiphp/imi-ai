<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\DTO;

use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Serializables;

/**
 * 训练的文件.
 */
#[
    Inherit,
    Serializables(mode: 'deny', fields: ['id', 'memberId', 'ip']),
]
class PublicEmbeddingProject extends EmbeddingProject
{
    use TMemberInfo;

    protected static ?string $saltClass = EmbeddingProject::class;
}
