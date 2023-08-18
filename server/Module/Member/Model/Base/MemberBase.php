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
 * @DDL(sql="CREATE TABLE `tb_member` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `status` tinyint unsigned NOT NULL COMMENT '状态',   `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '邮箱地址',   `email_hash` int unsigned NOT NULL COMMENT '邮箱哈希（crc32）',   `phone` bigint unsigned NOT NULL COMMENT '手机号码',   `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',   `nickname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '昵称',   `inviter_id` int unsigned NOT NULL DEFAULT '0' COMMENT '邀请人ID',   `inviter_time` int unsigned NOT NULL DEFAULT '0' COMMENT '邀请时间',   `register_time` int unsigned NOT NULL COMMENT '注册时间',   `register_ip_data` varbinary(16) NOT NULL COMMENT '注册IP数据',   `register_ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci GENERATED ALWAYS AS ((case length(`register_ip_data`) when 0 then _utf8mb4'' else inet6_ntoa(`register_ip_data`) end)) VIRTUAL NOT NULL COMMENT '注册IP',   `last_login_time` int unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',   `last_login_ip_data` varbinary(16) NOT NULL DEFAULT '' COMMENT '最后登录IP数据',   `last_login_ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci GENERATED ALWAYS AS ((case length(`last_login_ip_data`) when 0 then _utf8mb4'' else inet6_ntoa(`last_login_ip_data`) end)) VIRTUAL NOT NULL COMMENT '最后登录IP',   PRIMARY KEY (`id`) USING BTREE,   KEY `phone` (`phone`) USING BTREE,   KEY `email_hash` (`email_hash`) USING BTREE,   KEY `status` (`status`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户'")
 *
 * @property int|null    $id
 * @property int|null    $status          状态
 * @property string|null $email           邮箱地址
 * @property int|null    $emailHash       邮箱哈希（crc32）
 * @property int|null    $phone           手机号码
 * @property string|null $password        密码
 * @property string|null $nickname        昵称
 * @property int|null    $inviterId       邀请人ID
 * @property int|null    $inviterTime     邀请时间
 * @property int|null    $registerTime    注册时间
 * @property string|null $registerIpData  注册IP数据
 * @property string|null $registerIp      注册IP
 * @property int|null    $lastLoginTime   最后登录时间
 * @property string|null $lastLoginIpData 最后登录IP数据
 * @property string|null $lastLoginIp     最后登录IP
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
     * @Column(name="id", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
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
     * 状态.
     * status.
     *
     * @Column(name="status", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $status = null;

    /**
     * 获取 status - 状态.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * 赋值 status - 状态.
     *
     * @param int|null $status status
     *
     * @return static
     */
    public function setStatus($status)
    {
        $this->status = null === $status ? null : (int) $status;

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
     * @Column(name="email_hash", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
     * @Column(name="phone", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
     * 邀请人ID.
     * inviter_id.
     *
     * @Column(name="inviter_id", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $inviterId = 0;

    /**
     * 获取 inviterId - 邀请人ID.
     */
    public function getInviterId(): ?int
    {
        return $this->inviterId;
    }

    /**
     * 赋值 inviterId - 邀请人ID.
     *
     * @param int|null $inviterId inviter_id
     *
     * @return static
     */
    public function setInviterId($inviterId)
    {
        $this->inviterId = null === $inviterId ? null : (int) $inviterId;

        return $this;
    }

    /**
     * 邀请时间.
     * inviter_time.
     *
     * @Column(name="inviter_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $inviterTime = 0;

    /**
     * 获取 inviterTime - 邀请时间.
     */
    public function getInviterTime(): ?int
    {
        return $this->inviterTime;
    }

    /**
     * 赋值 inviterTime - 邀请时间.
     *
     * @param int|null $inviterTime inviter_time
     *
     * @return static
     */
    public function setInviterTime($inviterTime)
    {
        $this->inviterTime = null === $inviterTime ? null : (int) $inviterTime;

        return $this;
    }

    /**
     * 注册时间.
     * register_time.
     *
     * @Column(name="register_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
     * 注册IP数据.
     * register_ip_data.
     *
     * @Column(name="register_ip_data", type="varbinary", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $registerIpData = null;

    /**
     * 获取 registerIpData - 注册IP数据.
     */
    public function getRegisterIpData(): ?string
    {
        return $this->registerIpData;
    }

    /**
     * 赋值 registerIpData - 注册IP数据.
     *
     * @param string|null $registerIpData register_ip_data
     *
     * @return static
     */
    public function setRegisterIpData($registerIpData)
    {
        $this->registerIpData = null === $registerIpData ? null : (string) $registerIpData;

        return $this;
    }

    /**
     * 注册IP.
     * register_ip.
     *
     * @Column(name="register_ip", type="varchar", length=39, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=true)
     */
    protected ?string $registerIp = null;

    /**
     * 获取 registerIp - 注册IP.
     */
    public function getRegisterIp(): ?string
    {
        return $this->registerIp;
    }

    /**
     * 赋值 registerIp - 注册IP.
     *
     * @param string|null $registerIp register_ip
     *
     * @return static
     */
    public function setRegisterIp($registerIp)
    {
        if (\is_string($registerIp) && mb_strlen($registerIp) > 39)
        {
            throw new \InvalidArgumentException('The maximum length of $registerIp is 39');
        }
        $this->registerIp = null === $registerIp ? null : (string) $registerIp;

        return $this;
    }

    /**
     * 最后登录时间.
     * last_login_time.
     *
     * @Column(name="last_login_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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

    /**
     * 最后登录IP数据.
     * last_login_ip_data.
     *
     * @Column(name="last_login_ip_data", type="varbinary", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $lastLoginIpData = '';

    /**
     * 获取 lastLoginIpData - 最后登录IP数据.
     */
    public function getLastLoginIpData(): ?string
    {
        return $this->lastLoginIpData;
    }

    /**
     * 赋值 lastLoginIpData - 最后登录IP数据.
     *
     * @param string|null $lastLoginIpData last_login_ip_data
     *
     * @return static
     */
    public function setLastLoginIpData($lastLoginIpData)
    {
        $this->lastLoginIpData = null === $lastLoginIpData ? null : (string) $lastLoginIpData;

        return $this;
    }

    /**
     * 最后登录IP.
     * last_login_ip.
     *
     * @Column(name="last_login_ip", type="varchar", length=39, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=true)
     */
    protected ?string $lastLoginIp = null;

    /**
     * 获取 lastLoginIp - 最后登录IP.
     */
    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    /**
     * 赋值 lastLoginIp - 最后登录IP.
     *
     * @param string|null $lastLoginIp last_login_ip
     *
     * @return static
     */
    public function setLastLoginIp($lastLoginIp)
    {
        if (\is_string($lastLoginIp) && mb_strlen($lastLoginIp) > 39)
        {
            throw new \InvalidArgumentException('The maximum length of $lastLoginIp is 39');
        }
        $this->lastLoginIp = null === $lastLoginIp ? null : (string) $lastLoginIp;

        return $this;
    }
}
