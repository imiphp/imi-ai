<?php

declare(strict_types=1);

namespace app\Module\Email\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

abstract class Configs extends BaseEnum
{
    #[EnumItem(['text' => '发信邮箱', 'default' => ''])]
    public const EMAIL_FROM_ADDRESS = 'email_from_address';

    #[EnumItem(['text' => '发信人', 'default' => 'imi AI'])]
    public const EMAIL_FROM_NAME = 'email_from_name';

    #[EnumItem(['text' => 'SMTP服务器地址', 'default' => ''])]
    public const EMAIL_HOST = 'email_smtp_host';

    #[EnumItem(['text' => 'SMTP服务器端口', 'default' => 25])]
    public const EMAIL_PORT = 'email_port';

    #[EnumItem(['text' => 'SMTP安全协议', 'default' => ''])]
    public const EMAIL_SECURE = 'email_secure';

    #[EnumItem(['text' => '启用验证', 'default' => true])]
    public const EMAIL_AUTH = 'email_auth';

    #[EnumItem(['text' => 'SMTP用户名', 'default' => ''])]
    public const EMAIL_USERNAME = 'email_username';

    #[EnumItem(['text' => 'SMTP密码', 'default' => ''])]
    public const EMAIL_PASSWORD = 'email_password';
}
