<?php

declare(strict_types=1);

namespace app\Module\Chat\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

/**
 * 会话类型.
 */
class SessionType extends BaseEnum
{
    #[EnumItem(text: '聊天')]
    public const CHAT = 1;

    #[EnumItem(text: '提示语表单')]
    public const PROMPT_FORM = 2;
}
