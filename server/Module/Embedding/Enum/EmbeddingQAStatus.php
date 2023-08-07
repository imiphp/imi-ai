<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[PublicEnum(name: 'EmbeddingQAStatus')]
class EmbeddingQAStatus extends BaseEnum
{
    #[EnumItem(text: '正在回答')]
    public const ANSWERING = 1;

    #[EnumItem(text: '成功')]
    public const SUCCESS = 2;

    #[EnumItem(text: '失败')]
    public const FAILED = 3;
}
