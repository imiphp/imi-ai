<?php

declare(strict_types=1);

namespace app\Module\Card\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 卡类型 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Card\Model\CardType.name", default="tb_card_type"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Card\Model\CardType.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_card_type` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',   `amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '初始余额',   `expire_seconds` int unsigned NOT NULL COMMENT '激活后增加的有效时长，单位：秒',   `enable` bit(1) NOT NULL COMMENT '是否启用',   `system` bit(1) NOT NULL DEFAULT b'0' COMMENT '系统内置',   `member_activation_limit` int unsigned NOT NULL DEFAULT '0' COMMENT '每个用户最多激活次数，0代表不限制',   `create_time` int unsigned NOT NULL COMMENT '创建时间',   PRIMARY KEY (`id`) USING BTREE,   KEY `status` (`enable`,`create_time` DESC) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='卡类型'")
 *
 * @property int|null    $id
 * @property string|null $name                  名称
 * @property int|null    $amount                初始余额
 * @property int|null    $expireSeconds         激活后增加的有效时长，单位：秒
 * @property bool|null   $enable                是否启用
 * @property bool|null   $system                系统内置
 * @property int|null    $memberActivationLimit 每个用户最多激活次数，0代表不限制
 * @property int|null    $createTime            创建时间
 */
abstract class CardTypeBase extends Model
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
     * 名称.
     * name.
     *
     * @Column(name="name", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $name = null;

    /**
     * 获取 name - 名称.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * 赋值 name - 名称.
     *
     * @param string|null $name name
     *
     * @return static
     */
    public function setName($name)
    {
        if (\is_string($name) && mb_strlen($name) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $name is 32');
        }
        $this->name = null === $name ? null : (string) $name;

        return $this;
    }

    /**
     * 初始余额.
     * amount.
     *
     * @Column(name="amount", type="bigint", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $amount = 0;

    /**
     * 获取 amount - 初始余额.
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * 赋值 amount - 初始余额.
     *
     * @param int|null $amount amount
     *
     * @return static
     */
    public function setAmount($amount)
    {
        $this->amount = null === $amount ? null : (int) $amount;

        return $this;
    }

    /**
     * 激活后增加的有效时长，单位：秒.
     * expire_seconds.
     *
     * @Column(name="expire_seconds", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $expireSeconds = null;

    /**
     * 获取 expireSeconds - 激活后增加的有效时长，单位：秒.
     */
    public function getExpireSeconds(): ?int
    {
        return $this->expireSeconds;
    }

    /**
     * 赋值 expireSeconds - 激活后增加的有效时长，单位：秒.
     *
     * @param int|null $expireSeconds expire_seconds
     *
     * @return static
     */
    public function setExpireSeconds($expireSeconds)
    {
        $this->expireSeconds = null === $expireSeconds ? null : (int) $expireSeconds;

        return $this;
    }

    /**
     * 是否启用.
     * enable.
     *
     * @Column(name="enable", type="bit", length=1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?bool $enable = null;

    /**
     * 获取 enable - 是否启用.
     */
    public function getEnable(): ?bool
    {
        return $this->enable;
    }

    /**
     * 赋值 enable - 是否启用.
     *
     * @param bool|null $enable enable
     *
     * @return static
     */
    public function setEnable($enable)
    {
        $this->enable = null === $enable ? null : (bool) $enable;

        return $this;
    }

    /**
     * 系统内置.
     * system.
     *
     * @Column(name="system", type="bit", length=1, accuracy=0, nullable=false, default="b'0'", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?bool $system = false;

    /**
     * 获取 system - 系统内置.
     */
    public function getSystem(): ?bool
    {
        return $this->system;
    }

    /**
     * 赋值 system - 系统内置.
     *
     * @param bool|null $system system
     *
     * @return static
     */
    public function setSystem($system)
    {
        $this->system = null === $system ? null : (bool) $system;

        return $this;
    }

    /**
     * 每个用户最多激活次数，0代表不限制.
     * member_activation_limit.
     *
     * @Column(name="member_activation_limit", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $memberActivationLimit = 0;

    /**
     * 获取 memberActivationLimit - 每个用户最多激活次数，0代表不限制.
     */
    public function getMemberActivationLimit(): ?int
    {
        return $this->memberActivationLimit;
    }

    /**
     * 赋值 memberActivationLimit - 每个用户最多激活次数，0代表不限制.
     *
     * @param int|null $memberActivationLimit member_activation_limit
     *
     * @return static
     */
    public function setMemberActivationLimit($memberActivationLimit)
    {
        $this->memberActivationLimit = null === $memberActivationLimit ? null : (int) $memberActivationLimit;

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
}
