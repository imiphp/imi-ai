<?php

declare(strict_types=1);

namespace app\Module\VCode\Struct;

class VCodeTokenStore
{
    public function __construct(private string $vcode)
    {
    }

    public function getVcode(): string
    {
        return $this->vcode;
    }
}
