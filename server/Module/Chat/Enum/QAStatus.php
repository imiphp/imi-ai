<?php

declare(strict_types=1);

namespace app\Module\Chat\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

/**
 * 问答状态
 */
class QAStatus extends BaseEnum
{
    #[EnumItem(text: '提问')]
    public const ASK = 1;

    #[EnumItem(text: '回答')]
    public const ANSWER = 2;
}
