<?php

declare(strict_types=1);

namespace app\Module\Config\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 公开枚举.
 *
 * @Annotation
 *
 * @Target("CLASS")
 *
 * @property string $name 枚举名称
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PublicEnum extends Base
{
    public function __construct(?array $__data = null, string $name = '')
    {
        parent::__construct(...\func_get_args());
    }
}
