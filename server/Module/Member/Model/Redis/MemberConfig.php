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
    protected bool $emailRegister = true;

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
    protected int $registerCodeTTL = 3600;

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
    protected string $registerEmailTitle = 'imi AI 邮箱注册';

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
    protected string $registerEmailContent = <<<'HTML'
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
    protected bool $registerEmailIsHtml = true;

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
     * 启用邮箱找回密码.
     */
    #[Column]
    protected bool $emailForgot = true;

    public function getEmailForgot(): bool
    {
        return $this->emailForgot;
    }

    public function setEmailForgot(bool $emailForgot): self
    {
        $this->emailForgot = $emailForgot;

        return $this;
    }

    /**
     * 找回密码验证码有效时长.
     *
     * 单位：秒.
     */
    #[Column]
    protected int $forgotCodeTTL = 3600;

    public function getForgotCodeTTL(): int
    {
        return $this->forgotCodeTTL;
    }

    public function setForgotCodeTTL(int $forgotCodeTTL): self
    {
        $this->forgotCodeTTL = $forgotCodeTTL;

        return $this;
    }

    /**
     * 找回密码邮件标题.
     */
    #[Column]
    protected string $forgotEmailTitle = 'imi AI 邮箱找回密码';

    public function getForgotEmailTitle(): string
    {
        return $this->forgotEmailTitle;
    }

    public function setForgotEmailTitle(string $forgotEmailTitle): self
    {
        $this->forgotEmailTitle = $forgotEmailTitle;

        return $this;
    }

    /**
     * 找回密码邮件内容.
     */
    #[Column]
    protected string $forgotEmailContent = <<<'HTML'
    <p>验证码：<span style="color: #ff0000;">{code}</span></p>
    <p><a href="{url}" target="_blank">点我验证</a></p>
    HTML;

    public function getForgotEmailContent(): string
    {
        return $this->forgotEmailContent;
    }

    public function setForgotEmailContent(string $forgotEmailContent): self
    {
        $this->forgotEmailContent = $forgotEmailContent;

        return $this;
    }

    /**
     * 找回密码邮件是否html.
     */
    #[Column]
    protected bool $forgotEmailIsHtml = true;

    public function getForgotEmailIsHtml(): bool
    {
        return $this->forgotEmailIsHtml;
    }

    public function setForgotEmailIsHtml(bool $forgotEmailIsHtml): self
    {
        $this->forgotEmailIsHtml = $forgotEmailIsHtml;

        return $this;
    }

    /**
     * 登录Token有效时长.
     *
     * 单位：秒.
     */
    #[Column]
    protected int $tokenExpires = 30 * 86400;

    public function getTokenExpires(): int
    {
        return $this->tokenExpires;
    }

    public function setTokenExpires(int $tokenExpires): self
    {
        $this->tokenExpires = $tokenExpires;

        return $this;
    }

    /**
     * 启用邀请机制.
     */
    #[Column]
    protected bool $enableInvitation = true;

    public function getEnableInvitation(): bool
    {
        return $this->enableInvitation;
    }

    public function setEnableInvitation(bool $enableInvitation): self
    {
        $this->enableInvitation = $enableInvitation;

        return $this;
    }

    /**
     * 启用输入邀请机制.
     */
    #[Column]
    protected bool $enableInputInvitation = true;

    public function getEnableInputInvitation(): bool
    {
        return $this->enableInputInvitation;
    }

    public function setEnableInputInvitation(bool $enableInputInvitation): self
    {
        $this->enableInputInvitation = $enableInputInvitation;

        return $this;
    }

    /**
     * 邀请人获得奖励金额.
     */
    #[Column]
    protected int $inviterGiftAmount = 200000;

    public function getInviterGiftAmount(): int
    {
        return $this->inviterGiftAmount;
    }

    public function setInviterGiftAmount(int $inviterGiftAmount): self
    {
        $this->inviterGiftAmount = $inviterGiftAmount;

        return $this;
    }

    /**
     * 被邀请人获得奖励金额.
     */
    #[Column]
    protected int $inviteeGiftAmount = 100000;

    public function getInviteeGiftAmount(): int
    {
        return $this->inviteeGiftAmount;
    }

    public function setInviteeGiftAmount(int $inviteeGiftAmount): self
    {
        $this->inviteeGiftAmount = $inviteeGiftAmount;

        return $this;
    }

    /**
     * 启用邮箱黑名单.
     */
    #[Column]
    protected bool $enableEmailBlackList = true;

    public function getEnableEmailBlackList(): bool
    {
        return $this->enableEmailBlackList;
    }

    public function setEnableEmailBlackList(bool $enableEmailBlackList): self
    {
        $this->enableEmailBlackList = $enableEmailBlackList;

        return $this;
    }
}
