<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use Imi\Util\Traits\TNotRequiredDataToProperty;

class ModelConfig
{
    use TNotRequiredDataToProperty;

    public string $model = '';

    public bool $enable = true;

    /**
     * 需要付费才可使用.
     */
    public bool $paying = false;

    /**
     * 输入Token倍数，字符串小数.
     */
    public string|float $inputTokenMultiple = '1.0';

    /**
     * 输出Token倍数，字符串小数.
     */
    public string|float $outputTokenMultiple = '1.0';

    /**
     * 最大Token数.
     */
    public int $maxTokens = 0;

    /**
     * 提示文本.
     */
    public string $tips = '';
}
