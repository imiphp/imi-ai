<?php

declare(strict_types=1);

namespace app\Module\Admin\Enum;

use app\Module\Config\Annotation\AdminPublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[AdminPublicEnum(name: 'AdminOperationLogStatus')]
class OperationLogStatus extends BaseEnum
{
    #[EnumItem(text: '成功')]
    public const SUCCESS = 1;

    #[EnumItem(text: '失败')]
    public const FAIL = 2;
}
