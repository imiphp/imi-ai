<?php

declare(strict_types=1);

namespace app\Module\Embedding\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

class CompressFileTypes extends BaseEnum
{
    #[EnumItem(text: 'zip')]
    public const ZIP = 'zip';

    #[EnumItem(text: 'rar')]
    public const RAR = 'rar';

    #[EnumItem(text: '7z')]
    public const _7Z = '7z';

    #[EnumItem(text: 'xz')]
    public const XZ = 'xz';

    #[EnumItem(text: 'gz')]
    public const GZ = 'gz';

    #[EnumItem(text: 'bz')]
    public const BZ = 'bz';
}
