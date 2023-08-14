<?php

declare(strict_types=1);

namespace app\Module\Chat\Prompt\Annotation;

use Imi\Bean\Annotation\Base;

/**
 * 提示语采集器注解.
 *
 * @Annotation
 *
 * @Target("CLASS")
 *
 * @property string $title 配置标题
 * @property string $url   原地址
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PromptCrawler extends Base
{
    public function __construct(?array $__data = null, string $title = '', string $url = '')
    {
        parent::__construct(...\func_get_args());
    }
}
