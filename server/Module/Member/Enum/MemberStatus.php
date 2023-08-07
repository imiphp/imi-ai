<?php

declare(strict_types=1);

namespace app\Module\Member\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[PublicEnum(name: 'MemberStatus')]
class MemberStatus extends BaseEnum
{
    #[EnumItem(text: '正常')]
    public const NORMAL = 1;

    #[EnumItem(text: '封禁')]
    public const BANDED = 2;
}
