<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Client\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * OpenAI 客户端注解.
 *
 * @Annotation
 *
 * @Target("CLASS")
 *
 * @property string $title 配置标题
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class OpenAIClient extends Base
{
    public function __construct(?array $__data = null, string $title = '')
    {
        parent::__construct(...\func_get_args());
    }
}
