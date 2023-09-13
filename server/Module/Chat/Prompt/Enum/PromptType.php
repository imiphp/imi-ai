<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt\Enum;

use app\Module\Config\Annotation\PublicEnum;
use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

/**
 * 提示语类型.
 */
#[PublicEnum(name: 'PromptType')]
class PromptType extends BaseEnum
{
    #[EnumItem(text: '提示语')]
    public const PROMPT = 1;

    #[EnumItem(text: '工具')]
    public const TOOL = 2;
}
