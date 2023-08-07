<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[PublicEnum(name: 'EmbeddingStatus')]
class EmbeddingStatus extends BaseEnum
{
    #[EnumItem(text: '正在解压')]
    public const EXTRACTING = 1;

    #[EnumItem(text: '正在训练')]
    public const TRAINING = 2;

    #[EnumItem(text: '已完成')]
    public const COMPLETED = 3;

    #[EnumItem(text: '失败')]
    public const FAILED = 4;
}
