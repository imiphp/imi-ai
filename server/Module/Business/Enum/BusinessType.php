<?php

declare(strict_types=1);

namespace app\Module\Business\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

class BusinessType extends BaseEnum
{
    #[EnumItem(text: '其它')]
    public const OTHER = 1;

    #[EnumItem(text: 'AI聊天')]
    public const CHAT = 2;

    #[EnumItem(text: '模型训练')]
    public const EMBEDDING = 3;

    #[EnumItem(text: '模型训练聊天')]
    public const EMBEDDING_CHAT = 4;
}
