<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use Imi\Util\Traits\TNotRequiredDataToProperty;

class ModelConfig
{
    use TNotRequiredDataToProperty;

    public bool $enable = true;

    /**
     * 输入Token倍数，字符串小数.
     */
    public string $inputTokenMultiple = '1.0';

    /**
     * 输出Token倍数，字符串小数.
     */
    public string $outputTokenMultiple = '1.0';

    /**
     * 最大Token数.
     */
    public int $maxTokens = 0;

    /**
     * 提示文本.
     */
    public string $tips = '';
}
