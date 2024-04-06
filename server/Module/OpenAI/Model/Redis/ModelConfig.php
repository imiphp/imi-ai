<?php

declare(strict_types=1);

namespace app\Module\OpenAI\Model\Redis;

use Imi\Util\Traits\TNotRequiredDataToProperty;

#[\AllowDynamicProperties]
class ModelConfig
{
    use TNotRequiredDataToProperty;

    /**
     * 标题.
     */
    public string $title = '';

    /**
     * 实际模型名称.
     */
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

    /**
     * 每条消息额外的Tokens.
     */
    public int $additionalTokensPerMessage = 0;

    /**
     * 每次消息之后额外的Tokens.
     */
    public int $additionalTokensAfterMessages = 0;
}
