<?php

declare(strict_types=1);

namespace app\Module\Admin\Enum;

use Imi\Enum\BaseEnum;
use Imi\Enum\Annotation\EnumItem;
use app\Module\Config\Annotation\PublicEnum;
use app\Module\Config\Annotation\AdminPublicEnum;

#[AdminPublicEnum(name: 'AdminMemberStatus')]
class AdminMemberStatus extends BaseEnum
{
    #[EnumItem(text: '正常')]
    public const NORMAL = 1;

    #[EnumItem(text: '禁用')]
    public const DISABLED = 2;
}
