<?php

declare(strict_types=1);

namespace app\Module\Admin\Enum;

use app\Module\Config\Annotation\AdminPublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[AdminPublicEnum(name: 'AdminMemberStatus')]
class AdminMemberStatus extends BaseEnum
{
    #[EnumItem(text: '正常')]
    public const NORMAL = 1;

    #[EnumItem(text: '禁用')]
    public const DISABLED = 2;
}
