<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Admin;

use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;
use Imi\Config;
use Imi\Model\Annotation\Serializables;

/**
 * 文件训练项目.
 */
#[
    Inherit,
    Serializables(mode: 'deny'),
]
class EmbeddingProjectAdmin extends EmbeddingProject
{
    use TMemberInfo;

    protected static ?string $saltClass = parent::class;

    public function __setSecureField(bool $secureField): self
    {
        parent::__setSecureField($secureField);
        $this->memberInfo = null;
        $this->member->__setSecureField($secureField);

        return $this;
    }
}
