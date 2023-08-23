<?php

declare(strict_types=1);

namespace app\Module\Admin\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 后台用户 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Admin\Model\AdminMember.name", default="tb_admin_member"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Admin\Model\AdminMember.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_admin_member` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `account` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '用户名',   `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '密码',   `nickname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '昵称',   `status` tinyint unsigned NOT NULL COMMENT '状态',   `create_time` int unsigned NOT NULL COMMENT '创建时间',   `delete_time` int unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',   `last_login_time` int unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',   `last_login_ip_data` varbinary(16) NOT NULL DEFAULT '' COMMENT '最后登录IP数据',   `last_login_ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci GENERATED ALWAYS AS ((case length(`last_login_ip_data`) when 0 then _utf8mb4'' else inet6_ntoa(`last_login_ip_data`) end)) VIRTUAL NOT NULL COMMENT '最后登录IP',   PRIMARY KEY (`id`),   UNIQUE KEY `account` (`account`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='后台用户'")
 *
 * @property int|null    $id
 * @property string|null $account         用户名
 * @property string|null $password        密码
 * @property string|null $nickname        昵称
 * @property int|null    $status          状态
 * @property int|null    $createTime      创建时间
 * @property int|null    $deleteTime      删除时间
 * @property int|null    $lastLoginTime   最后登录时间
 * @property string|null $lastLoginIpData 最后登录IP数据
 * @property string|null $lastLoginIp     最后登录IP
 */
abstract class AdminMemberBase extends Model
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
     * 用户名.
     * account.
     *
     * @Column(name="account", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $account = null;

    /**
     * 获取 account - 用户名.
     */
    public function getAccount(): ?string
    {
        return $this->account;
    }

    /**
     * 赋值 account - 用户名.
     *
     * @param string|null $account account
     *
     * @return static
     */
    public function setAccount($account)
    {
        if (\is_string($account) && mb_strlen($account) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $account is 32');
        }
        $this->account = null === $account ? null : (string) $account;

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
    protected ?string $nickname = '';

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
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $createTime = null;

    /**
     * 获取 createTime - 创建时间.
     */
    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    /**
     * 赋值 createTime - 创建时间.
     *
     * @param int|null $createTime create_time
     *
     * @return static
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = null === $createTime ? null : (int) $createTime;

        return $this;
    }

    /**
     * 删除时间.
     * delete_time.
     *
     * @Column(name="delete_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $deleteTime = 0;

    /**
     * 获取 deleteTime - 删除时间.
     */
    public function getDeleteTime(): ?int
    {
        return $this->deleteTime;
    }

    /**
     * 赋值 deleteTime - 删除时间.
     *
     * @param int|null $deleteTime delete_time
     *
     * @return static
     */
    public function setDeleteTime($deleteTime)
    {
        $this->deleteTime = null === $deleteTime ? null : (int) $deleteTime;

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
