<?php

declare(strict_types=1);

namespace app\Module\Member\Enum;

use Imi\Enum\Annotation\EnumItem;
use Imi\Enum\BaseEnum;

abstract class Configs extends BaseEnum
{
    #[EnumItem(['text' => '启用邮箱注册', 'default' => true])]
    public const EMAIL_REGISTER = 'email_register';

    /**
     * 注册验证码有效时长.
     *
     * 单位：秒.
     */
    #[EnumItem(['text' => '注册验证码有效时长', 'default' => 3600])]
    public const REGISTER_CODE_TTL = 'register_code_ttl';

    #[EnumItem(['text' => '注册邮件标题', 'default' => 'imi AI 邮箱注册'])]
    public const EMAIL_TITLE = 'email_title';

    #[EnumItem(['text' => '注册邮件内容', 'default' => <<<'HTML'
    <p>验证码：<span style="color: #ff0000;">{code}</span></p>
    <p><a href="{url}" target="_blank">点我验证</a></p>
    HTML])]
    public const EMAIL_CONTENT = 'email_content';

    #[EnumItem(['text' => '注册邮件是否html', 'default' => true])]
    public const EMAIL_IS_HTML = 'email_is_html';

    /**
     * 登录Token有效时长.
     *
     * 单位：秒.
     */
    #[EnumItem(['text' => '登录Token有效时长', 'default' => 30 * 86400])]
    public const TOKEN_EXPIRES = 'token_expires';
}
