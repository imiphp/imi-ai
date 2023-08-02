<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model;

use app\Module\Embedding\Enum\PublicProjectStatus;
use app\Module\Embedding\Model\Base\EmbeddingPublicProjectBase;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 公共项目.
 *
 * @Inherit
 */
#[
    Serializables(mode: 'deny', fields: ['projectId']),
]
class EmbeddingPublicProject extends EmbeddingPublicProjectBase
{
    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return PublicProjectStatus::getText($this->status);
    }
}
