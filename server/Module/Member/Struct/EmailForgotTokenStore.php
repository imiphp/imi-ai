<?php

declare(strict_types=1);

namespace app\Module\Member\Struct;

class EmailForgotTokenStore
{
    public function __construct(private string $email, private string $password, private string $code, private string $verifyToken)
    {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getVerifyToken(): string
    {
        return $this->verifyToken;
    }
}
