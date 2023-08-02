<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

class PublicProjectStatus extends BaseEnum
{
    #[EnumItem(text: '已开放')]
    public const OPEN = 1;

    #[EnumItem(text: '已关闭')]
    public const CLOSED = 2;

    #[EnumItem(text: '等待审核')]
    public const WAIT_FOR_REVIEW = 3;

    #[EnumItem(text: '审核失败')]
    public const REVIEW_FAILED = 4;
}
