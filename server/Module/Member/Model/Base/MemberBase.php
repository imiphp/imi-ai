<?php

declare(strict_types=1);

namespace app\Module\Member\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 用户 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Member\Model\Member.name", default="tb_member"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Member\Model\Member.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_member` (   `id` int(10) unsigned NOT NULL AUTO_INCREMENT,   `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱地址',   `email_hash` int(10) unsigned NOT NULL COMMENT '邮箱哈希（crc32）',   `phone` bigint(20) unsigned NOT NULL COMMENT '手机号码',   `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',   `nickname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '昵称',   `register_time` int(10) unsigned NOT NULL COMMENT '注册时间',   `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',   PRIMARY KEY (`id`),   KEY `phone` (`phone`),   KEY `email_hash` (`email_hash`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户'")
 *
 * @property int|null    $id
 * @property string|null $email         邮箱地址
 * @property int|null    $emailHash     邮箱哈希（crc32）
 * @property int|null    $phone         手机号码
 * @property string|null $password      密码
 * @property string|null $nickname      昵称
 * @property int|null    $registerTime  注册时间
 * @property int|null    $lastLoginTime 最后登录时间
 */
abstract class MemberBase extends Model
{
    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEY = 'id';

    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEYS = ['id'];

    /**
     * id.
     *
     * @Column(name="id", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
     */
    protected ?int $id = null;

    /**
     * 获取 id.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * 赋值 id.
     *
     * @param int|null $id id
     *
     * @return static
     */
    public function setId($id)
    {
        $this->id = null === $id ? null : (int) $id;

        return $this;
    }

    /**
     * 邮箱地址.
     * email.
     *
     * @Column(name="email", type="varchar", length=255, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $email = null;

    /**
     * 获取 email - 邮箱地址.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * 赋值 email - 邮箱地址.
     *
     * @param string|null $email email
     *
     * @return static
     */
    public function setEmail($email)
    {
        if (\is_string($email) && mb_strlen($email) > 255)
        {
            throw new \InvalidArgumentException('The maximum length of $email is 255');
        }
        $this->email = null === $email ? null : (string) $email;

        return $this;
    }

    /**
     * 邮箱哈希（crc32）.
     * email_hash.
     *
     * @Column(name="email_hash", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $emailHash = null;

    /**
     * 获取 emailHash - 邮箱哈希（crc32）.
     */
    public function getEmailHash(): ?int
    {
        return $this->emailHash;
    }

    /**
     * 赋值 emailHash - 邮箱哈希（crc32）.
     *
     * @param int|null $emailHash email_hash
     *
     * @return static
     */
    public function setEmailHash($emailHash)
    {
        $this->emailHash = null === $emailHash ? null : (int) $emailHash;

        return $this;
    }

    /**
     * 手机号码.
     * phone.
     *
     * @Column(name="phone", type="bigint", length=20, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $phone = null;

    /**
     * 获取 phone - 手机号码.
     */
    public function getPhone(): ?int
    {
        return $this->phone;
    }

    /**
     * 赋值 phone - 手机号码.
     *
     * @param int|null $phone phone
     *
     * @return static
     */
    public function setPhone($phone)
    {
        $this->phone = null === $phone ? null : (int) $phone;

        return $this;
    }

    /**
     * 密码.
     * password.
     *
     * @Column(name="password", type="varchar", length=255, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $password = null;

    /**
     * 获取 password - 密码.
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * 赋值 password - 密码.
     *
     * @param string|null $password password
     *
     * @return static
     */
    public function setPassword($password)
    {
        if (\is_string($password) && mb_strlen($password) > 255)
        {
            throw new \InvalidArgumentException('The maximum length of $password is 255');
        }
        $this->password = null === $password ? null : (string) $password;

        return $this;
    }

    /**
     * 昵称.
     * nickname.
     *
     * @Column(name="nickname", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $nickname = null;

    /**
     * 获取 nickname - 昵称.
     */
    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    /**
     * 赋值 nickname - 昵称.
     *
     * @param string|null $nickname nickname
     *
     * @return static
     */
    public function setNickname($nickname)
    {
        if (\is_string($nickname) && mb_strlen($nickname) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $nickname is 32');
        }
        $this->nickname = null === $nickname ? null : (string) $nickname;

        return $this;
    }

    /**
     * 注册时间.
     * register_time.
     *
     * @Column(name="register_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $registerTime = null;

    /**
     * 获取 registerTime - 注册时间.
     */
    public function getRegisterTime(): ?int
    {
        return $this->registerTime;
    }

    /**
     * 赋值 registerTime - 注册时间.
     *
     * @param int|null $registerTime register_time
     *
     * @return static
     */
    public function setRegisterTime($registerTime)
    {
        $this->registerTime = null === $registerTime ? null : (int) $registerTime;

        return $this;
    }

    /**
     * 最后登录时间.
     * last_login_time.
     *
     * @Column(name="last_login_time", type="int", length=10, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $lastLoginTime = 0;

    /**
     * 获取 lastLoginTime - 最后登录时间.
     */
    public function getLastLoginTime(): ?int
    {
        return $this->lastLoginTime;
    }

    /**
     * 赋值 lastLoginTime - 最后登录时间.
     *
     * @param int|null $lastLoginTime last_login_time
     *
     * @return static
     */
    public function setLastLoginTime($lastLoginTime)
    {
        $this->lastLoginTime = null === $lastLoginTime ? null : (int) $lastLoginTime;

        return $this;
    }
}
