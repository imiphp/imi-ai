<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

class SupportFileTypes extends BaseEnum
{
    #[EnumItem(text: 'txt')]
    public const TXT = 'txt';
}
