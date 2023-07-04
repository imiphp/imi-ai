<?php

declare(strict_types=1);

namespace app\Module\VCode\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

abstract class Configs extends BaseEnum
{
    /**
     * 验证码有效时长.
     *
     * 单位：秒.
     */
    #[EnumItem(['text' => '验证码有效时长', 'default' => 300])]
    public const VCODE_TTL = 'vcode_ttl';
}
