<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Admin;

use app\Module\Embedding\Model\EmbeddingProject;
use app\Module\Embedding\Model\EmbeddingPublicProject;
use app\Module\Member\Model\Traits\TMemberInfo;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializables;

/**
 * 文件训练项目.
 */
#[
    Inherit,
    Serializables(mode: 'deny'),
]
class EmbeddingProjectAdminWithPublic extends EmbeddingProject
{
    use TMemberInfo;

    protected static ?string $saltClass = parent::class;

    #[
        OneToOne(model: EmbeddingPublicProject::class, with: true),
        JoinFrom(field: 'id'),
        JoinTo(field: 'project_id'),
    ]
    protected ?EmbeddingPublicProject $publicProject = null;

    public function getPublicProject(): ?EmbeddingPublicProject
    {
        return $this->publicProject;
    }

    public function setPublicProject(?EmbeddingPublicProject $publicProject): self
    {
        $this->publicProject = $publicProject;

        return $this;
    }

    public function __setSecureField(bool $secureField): self
    {
        parent::__setSecureField($secureField);
        $this->memberInfo = null;
        $this->member->__setSecureField($secureField);

        return $this;
    }
}
