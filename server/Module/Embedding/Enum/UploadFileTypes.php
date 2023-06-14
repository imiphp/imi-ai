<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

class UploadFileTypes extends BaseEnum
{
    #[EnumItem(text: 'zip')]
    public const ZIP = 'zip';
}
