<?php

declare(strict_types=1);

namespace app\Module\Chat\Util;

use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;

class Gpt3Tokenizer extends \Gioni06\Gpt3Tokenizer\Gpt3Tokenizer
{
    public function __construct()
    {
        parent::__construct(new Gpt3TokenizerConfig());
    }

    public static function getInstance(): self
    {
        return new self();
    }
}
