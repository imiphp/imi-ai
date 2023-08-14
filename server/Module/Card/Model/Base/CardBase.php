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
 * 卡 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Card\Model\Card.name", default="tb_card"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Card\Model\Card.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_card` (   `id` bigint unsigned NOT NULL AUTO_INCREMENT,   `type` int unsigned NOT NULL COMMENT '卡类型',   `member_id` int unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',   `amount` bigint NOT NULL COMMENT '初始金额',   `left_amount` bigint NOT NULL COMMENT '剩余金额',   `create_time` int unsigned NOT NULL COMMENT '创建时间',   `activation_time` int unsigned NOT NULL DEFAULT '0' COMMENT '激活时间',   `expire_time` int unsigned NOT NULL COMMENT '过期时间',   PRIMARY KEY (`id`) USING BTREE,   KEY `member_id` (`member_id`,`expire_time`,`left_amount`) USING BTREE,   KEY `type` (`member_id`,`type`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='卡'")
 *
 * @property int|null $id
 * @property int|null $type           卡类型
 * @property int|null $memberId       用户ID
 * @property int|null $amount         初始金额
 * @property int|null $leftAmount     剩余金额
 * @property int|null $createTime     创建时间
 * @property int|null $activationTime 激活时间
 * @property int|null $expireTime     过期时间
 */
abstract class CardBase extends Model
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
     * @Column(name="id", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
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
     * 卡类型.
     * type.
     *
     * @Column(name="type", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $type = null;

    /**
     * 获取 type - 卡类型.
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * 赋值 type - 卡类型.
     *
     * @param int|null $type type
     *
     * @return static
     */
    public function setType($type)
    {
        $this->type = null === $type ? null : (int) $type;

        return $this;
    }

    /**
     * 用户ID.
     * member_id.
     *
     * @Column(name="member_id", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $memberId = 0;

    /**
     * 获取 memberId - 用户ID.
     */
    public function getMemberId(): ?int
    {
        return $this->memberId;
    }

    /**
     * 赋值 memberId - 用户ID.
     *
     * @param int|null $memberId member_id
     *
     * @return static
     */
    public function setMemberId($memberId)
    {
        $this->memberId = null === $memberId ? null : (int) $memberId;

        return $this;
    }

    /**
     * 初始金额.
     * amount.
     *
     * @Column(name="amount", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?int $amount = null;

    /**
     * 获取 amount - 初始金额.
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * 赋值 amount - 初始金额.
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
     * 剩余金额.
     * left_amount.
     *
     * @Column(name="left_amount", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?int $leftAmount = null;

    /**
     * 获取 leftAmount - 剩余金额.
     */
    public function getLeftAmount(): ?int
    {
        return $this->leftAmount;
    }

    /**
     * 赋值 leftAmount - 剩余金额.
     *
     * @param int|null $leftAmount left_amount
     *
     * @return static
     */
    public function setLeftAmount($leftAmount)
    {
        $this->leftAmount = null === $leftAmount ? null : (int) $leftAmount;

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
     * 激活时间.
     * activation_time.
     *
     * @Column(name="activation_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $activationTime = 0;

    /**
     * 获取 activationTime - 激活时间.
     */
    public function getActivationTime(): ?int
    {
        return $this->activationTime;
    }

    /**
     * 赋值 activationTime - 激活时间.
     *
     * @param int|null $activationTime activation_time
     *
     * @return static
     */
    public function setActivationTime($activationTime)
    {
        $this->activationTime = null === $activationTime ? null : (int) $activationTime;

        return $this;
    }

    /**
     * 过期时间.
     * expire_time.
     *
     * @Column(name="expire_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $expireTime = null;

    /**
     * 获取 expireTime - 过期时间.
     */
    public function getExpireTime(): ?int
    {
        return $this->expireTime;
    }

    /**
     * 赋值 expireTime - 过期时间.
     *
     * @param int|null $expireTime expire_time
     *
     * @return static
     */
    public function setExpireTime($expireTime)
    {
        $this->expireTime = null === $expireTime ? null : (int) $expireTime;

        return $this;
    }
}
