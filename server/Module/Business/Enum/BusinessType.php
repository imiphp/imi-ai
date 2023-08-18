<?php

declare(strict_types=1);

namespace app\Module\Business\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

#[PublicEnum(name: 'BusinessType')]
class BusinessType extends BaseEnum
{
    #[EnumItem(text: '其它')]
    public const OTHER = 1;

    #[EnumItem(text: 'AI聊天')]
    public const CHAT = 2;

    #[EnumItem(text: '模型训练')]
    public const EMBEDDING = 3;

    #[EnumItem(text: '模型训练对话')]
    public const EMBEDDING_CHAT = 4;

    #[EnumItem(text: '邀请注册')]
    public const INVITER = 100;

    #[EnumItem(text: '受邀注册')]
    public const INVITEE = 101;
}
