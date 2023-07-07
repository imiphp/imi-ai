<?php

declare(strict_types=1);

namespace app\Module\Email\Model\Redis;

use app\Module\Config\Annotation\ConfigModel;
use app\Module\Config\Model\Redis\Traits\TConfigModel;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\RedisEntity;
use Imi\Model\Annotation\Serializables;
use Imi\Model\RedisModel;

#[
    Entity(),
    RedisEntity(key: 'config:email', storage: 'hash_object'),
    ConfigModel(title: '邮箱设置'),
    Serializables(mode: 'allow'),
]
class EmailConfig extends RedisModel
{
    use TConfigModel;

    /**
     * 发信邮箱.
     */
    #[Column]
    public string $fromAddress = '';

    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    public function setFromAddress(string $fromAddress): self
    {
        $this->fromAddress = $fromAddress;

        return $this;
    }

    /**
     * 发信人.
     */
    #[Column]
    public string $fromName = '';

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;

        return $this;
    }

    /**
     * SMTP服务器地址.
     */
    #[Column]
    public string $host = '';

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * SMTP服务器端口.
     */
    #[Column]
    public int $port = 25;

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * SMTP安全协议.
     */
    #[Column]
    public string $secure = '';

    public function getSecure(): string
    {
        return $this->secure;
    }

    public function setSecure(string $secure): self
    {
        $this->secure = $secure;

        return $this;
    }

    /**
     * 启用验证.
     */
    #[Column]
    public bool $auth = true;

    public function getAuth(): bool
    {
        return $this->auth;
    }

    public function setAuth(bool $auth): self
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * SMTP用户名.
     */
    #[Column]
    public string $username = '';

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * SMTP密码.
     */
    #[Column]
    public string $password = '';

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
