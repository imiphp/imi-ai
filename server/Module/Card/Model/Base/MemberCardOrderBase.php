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
 * 用户卡订单 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Card\Model\MemberCardOrder.name", default="tb_member_card_order"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Card\Model\MemberCardOrder.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_member_card_order` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `member_id` int(10) unsigned NOT NULL COMMENT '用户ID',   `operation_type` tinyint(3) unsigned NOT NULL COMMENT '操作类型',   `business_type` tinyint(3) unsigned NOT NULL COMMENT '业务类型',   `business_id` bigint(20) unsigned NOT NULL COMMENT '业务ID',   `change_amount` bigint(20) NOT NULL COMMENT '变动余额',   `detail_ids` json NOT NULL COMMENT '操作涉及的明细记录ID',   `time` int(10) unsigned NOT NULL COMMENT '时间',   PRIMARY KEY (`id`),   KEY `member_id` (`member_id`,`operation_type`,`business_type`,`time`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户卡订单'")
 *
 * @property int|null                                    $id
 * @property int|null                                    $memberId      用户ID
 * @property int|null                                    $operationType 操作类型
 * @property int|null                                    $businessType  业务类型
 * @property int|null                                    $businessId    业务ID
 * @property int|null                                    $changeAmount  变动余额
 * @property \Imi\Util\LazyArrayObject|object|array|null $detailIds     操作涉及的明细记录ID
 * @property int|null                                    $time          时间
 */
abstract class MemberCardOrderBase extends Model
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
     * @Column(name="id", type="bigint", length=20, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
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
     * 用户ID.
     * member_id.
     *
     * @Column(name="member_id", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $memberId = null;

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
     * 操作类型.
     * operation_type.
     *
     * @Column(name="operation_type", type="tinyint", length=3, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $operationType = null;

    /**
     * 获取 operationType - 操作类型.
     */
    public function getOperationType(): ?int
    {
        return $this->operationType;
    }

    /**
     * 赋值 operationType - 操作类型.
     *
     * @param int|null $operationType operation_type
     *
     * @return static
     */
    public function setOperationType($operationType)
    {
        $this->operationType = null === $operationType ? null : (int) $operationType;

        return $this;
    }

    /**
     * 业务类型.
     * business_type.
     *
     * @Column(name="business_type", type="tinyint", length=3, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $businessType = null;

    /**
     * 获取 businessType - 业务类型.
     */
    public function getBusinessType(): ?int
    {
        return $this->businessType;
    }

    /**
     * 赋值 businessType - 业务类型.
     *
     * @param int|null $businessType business_type
     *
     * @return static
     */
    public function setBusinessType($businessType)
    {
        $this->businessType = null === $businessType ? null : (int) $businessType;

        return $this;
    }

    /**
     * 业务ID.
     * business_id.
     *
     * @Column(name="business_id", type="bigint", length=20, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $businessId = null;

    /**
     * 获取 businessId - 业务ID.
     */
    public function getBusinessId(): ?int
    {
        return $this->businessId;
    }

    /**
     * 赋值 businessId - 业务ID.
     *
     * @param int|null $businessId business_id
     *
     * @return static
     */
    public function setBusinessId($businessId)
    {
        $this->businessId = null === $businessId ? null : (int) $businessId;

        return $this;
    }

    /**
     * 变动余额.
     * change_amount.
     *
     * @Column(name="change_amount", type="bigint", length=20, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?int $changeAmount = null;

    /**
     * 获取 changeAmount - 变动余额.
     */
    public function getChangeAmount(): ?int
    {
        return $this->changeAmount;
    }

    /**
     * 赋值 changeAmount - 变动余额.
     *
     * @param int|null $changeAmount change_amount
     *
     * @return static
     */
    public function setChangeAmount($changeAmount)
    {
        $this->changeAmount = null === $changeAmount ? null : (int) $changeAmount;

        return $this;
    }

    /**
     * 操作涉及的明细记录ID.
     * detail_ids.
     *
     * @Column(name="detail_ids", type="json", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     *
     * @var \Imi\Util\LazyArrayObject|object|array|null
     */
    protected $detailIds = null;

    /**
     * 获取 detailIds - 操作涉及的明细记录ID.
     *
     * @return \Imi\Util\LazyArrayObject|object|array|null
     */
    public function &getDetailIds()
    {
        return $this->detailIds;
    }

    /**
     * 赋值 detailIds - 操作涉及的明细记录ID.
     *
     * @param \Imi\Util\LazyArrayObject|object|array|null $detailIds detail_ids
     *
     * @return static
     */
    public function setDetailIds($detailIds)
    {
        $this->detailIds = null === $detailIds ? null : $detailIds;

        return $this;
    }

    /**
     * 时间.
     * time.
     *
     * @Column(name="time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $time = null;

    /**
     * 获取 time - 时间.
     */
    public function getTime(): ?int
    {
        return $this->time;
    }

    /**
     * 赋值 time - 时间.
     *
     * @param int|null $time time
     *
     * @return static
     */
    public function setTime($time)
    {
        $this->time = null === $time ? null : (int) $time;

        return $this;
    }
}
