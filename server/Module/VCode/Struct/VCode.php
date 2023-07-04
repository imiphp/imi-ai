<?php

declare(strict_types=1);

namespace app\Module\VCode\Struct;

class VCode
{
    public function __construct(private string $image, private string $vcode, private string $token)
    {
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getVcode(): string
    {
        return $this->vcode;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
