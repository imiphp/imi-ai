<?php

declare(strict_types=1);

namespace app\Module\Member\Struct;

class EmailRegisterTokenStore
{
    public function __construct(private string $email, private string $password, private string $code, private string $verifyToken, private string $invitationCode = '')
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

    public function getInvitationCode(): string
    {
        return $this->invitationCode;
    }
}
