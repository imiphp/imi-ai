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
 * 用户卡退款订单 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Card\Model\MemberCardRefundOrder.name", default="tb_member_card_refund_order"), usePrefix=false, id={"refund_order_id", "pay_order_id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Card\Model\MemberCardRefundOrder.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_member_card_refund_order` (   `refund_order_id` bigint unsigned NOT NULL,   `pay_order_id` bigint unsigned NOT NULL,   PRIMARY KEY (`refund_order_id`,`pay_order_id`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='用户卡退款订单'")
 *
 * @property int|null $refundOrderId
 * @property int|null $payOrderId
 */
abstract class MemberCardRefundOrderBase extends Model
{
    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEY = 'refund_order_id';

    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEYS = ['refund_order_id', 'pay_order_id'];

    /**
     * refund_order_id.
     *
     * @Column(name="refund_order_id", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $refundOrderId = null;

    /**
     * 获取 refundOrderId.
     */
    public function getRefundOrderId(): ?int
    {
        return $this->refundOrderId;
    }

    /**
     * 赋值 refundOrderId.
     *
     * @param int|null $refundOrderId refund_order_id
     *
     * @return static
     */
    public function setRefundOrderId($refundOrderId)
    {
        $this->refundOrderId = null === $refundOrderId ? null : (int) $refundOrderId;

        return $this;
    }

    /**
     * pay_order_id.
     *
     * @Column(name="pay_order_id", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $payOrderId = null;

    /**
     * 获取 payOrderId.
     */
    public function getPayOrderId(): ?int
    {
        return $this->payOrderId;
    }

    /**
     * 赋值 payOrderId.
     *
     * @param int|null $payOrderId pay_order_id
     *
     * @return static
     */
    public function setPayOrderId($payOrderId)
    {
        $this->payOrderId = null === $payOrderId ? null : (int) $payOrderId;

        return $this;
    }
}
