<?php

declare(strict_types=1);

namespace app\Module\Config\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 配置模型注解.
 *
 * @Annotation
 *
 * @Target("CLASS")
 *
 * @property string $title 配置标题
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ConfigModel extends Base
{
    public function __construct(?array $__data = null, string $title = '')
    {
        parent::__construct(...\func_get_args());
    }
}
