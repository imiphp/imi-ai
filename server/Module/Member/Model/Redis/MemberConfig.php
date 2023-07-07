<?php

declare(strict_types=1);

namespace app\Module\Member\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\Annotation\Serializables;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:member', storage: 'hash_object'),
    ConfigModel(title: '账户设置'),
    Serializables(mode: 'deny', fields: ['registerEmailTitle', 'registerEmailContent', 'registerEmailIsHtml']),
]
class MemberConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 启用邮箱注册.
     */
    #[Column]
    public bool $emailRegister = true;

    public function getEmailRegister(): bool
    {
        return $this->emailRegister;
    }

    public function setEmailRegister(bool $emailRegister): self
    {
        $this->emailRegister = $emailRegister;

        return $this;
    }

    /**
     * 注册验证码有效时长.
     *
     * 单位：秒.
     */
    #[Column]
    public int $registerCodeTTL = 3600;

    public function getRegisterCodeTTL(): int
    {
        return $this->registerCodeTTL;
    }

    public function setRegisterCodeTTL(int $registerCodeTTL): self
    {
        $this->registerCodeTTL = $registerCodeTTL;

        return $this;
    }

    /**
     * 注册邮件标题.
     */
    #[Column]
    public string $registerEmailTitle = 'imi AI 邮箱注册';

    public function getRegisterEmailTitle(): string
    {
        return $this->registerEmailTitle;
    }

    public function setRegisterEmailTitle(string $registerEmailTitle): self
    {
        $this->registerEmailTitle = $registerEmailTitle;

        return $this;
    }

    /**
     * 注册邮件内容.
     */
    #[Column]
    public string $registerEmailContent = <<<'HTML'
    <p>验证码：<span style="color: #ff0000;">{code}</span></p>
    <p><a href="{url}" target="_blank">点我验证</a></p>
    HTML;

    public function getRegisterEmailContent(): string
    {
        return $this->registerEmailContent;
    }

    public function setRegisterEmailContent(string $registerEmailContent): self
    {
        $this->registerEmailContent = $registerEmailContent;

        return $this;
    }

    /**
     * 注册邮件是否html.
     */
    #[Column]
    public bool $registerEmailIsHtml = true;

    public function getRegisterEmailIsHtml(): bool
    {
        return $this->registerEmailIsHtml;
    }

    public function setRegisterEmailIsHtml(bool $registerEmailIsHtml): self
    {
        $this->registerEmailIsHtml = $registerEmailIsHtml;

        return $this;
    }

    /**
     * 登录Token有效时长.
     *
     * 单位：秒.
     */
    #[Column]
    public int $tokenExpires = 30 * 86400;

    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    public function setTokenExpires(int $tokenExpires): self
    {
        $this->tokenExpires = $tokenExpires;

        return $this;
    }
}
