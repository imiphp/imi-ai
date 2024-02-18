<?php

declare(strict_types=1);

namespace app\Module\Payment\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 支付订单 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Payment\Model\PaymentOrder.name", default="tb_payment_order"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Payment\Model\PaymentOrder.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_payment_order` (   `id` bigint unsigned NOT NULL AUTO_INCREMENT,   `type` tinyint unsigned NOT NULL COMMENT '订单类型',   `channel_id` tinyint unsigned NOT NULL COMMENT '支付渠道ID',   `secondary_channel_id` tinyint unsigned NOT NULL COMMENT '二级支付渠道ID',   `tertiary_channel_id` tinyint unsigned NOT NULL COMMENT '三级支付渠道ID',   `trade_no` char(24) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '交易单号',   `channel_trade_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '支付渠道交易单号',   `secondary_trade_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '二级支付渠道交易单号',   `tertiary_trade_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '三级支付渠道交易单号',   `business_type` tinyint unsigned NOT NULL COMMENT '业务类型',   `business_id` bigint unsigned NOT NULL COMMENT '业务记录ID',   `member_id` int NOT NULL COMMENT '用户ID',   `amount` int NOT NULL COMMENT '金额',   `left_amount` int NOT NULL COMMENT '剩余金额',   `remark` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注',   `create_time` int unsigned NOT NULL COMMENT '交易订单创建时间',   `pay_time` int unsigned NOT NULL DEFAULT '0' COMMENT '支付平台方返回的真实支付时间',   `notify_time` int unsigned NOT NULL DEFAULT '0' COMMENT '我方接收到通知的时间',   PRIMARY KEY (`id`),   UNIQUE KEY `trade_no` (`trade_no`),   KEY `member_id` (`member_id`,`business_type`,`tertiary_channel_id`,`secondary_channel_id`,`channel_id`,`type`,`trade_no`,`channel_trade_no`,`secondary_trade_no`,`tertiary_trade_no`,`create_time` DESC) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付订单'")
 *
 * @property int|null    $id
 * @property int|null    $type               订单类型
 * @property int|null    $channelId          支付渠道ID
 * @property int|null    $secondaryChannelId 二级支付渠道ID
 * @property int|null    $tertiaryChannelId  三级支付渠道ID
 * @property string|null $tradeNo            交易单号
 * @property string|null $channelTradeNo     支付渠道交易单号
 * @property string|null $secondaryTradeNo   二级支付渠道交易单号
 * @property string|null $tertiaryTradeNo    三级支付渠道交易单号
 * @property int|null    $businessType       业务类型
 * @property int|null    $businessId         业务记录ID
 * @property int|null    $memberId           用户ID
 * @property int|null    $amount             金额
 * @property int|null    $leftAmount         剩余金额
 * @property string|null $remark             备注
 * @property int|null    $createTime         交易订单创建时间
 * @property int|null    $payTime            支付平台方返回的真实支付时间
 * @property int|null    $notifyTime         我方接收到通知的时间
 */
abstract class PaymentOrderBase extends Model
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
     * 订单类型.
     * type.
     *
     * @Column(name="type", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $type = null;

    /**
     * 获取 type - 订单类型.
     */
    public function getType(): ?int
    {
        return $this->type;
    }

    /**
     * 赋值 type - 订单类型.
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
     * 支付渠道ID.
     * channel_id.
     *
     * @Column(name="channel_id", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $channelId = null;

    /**
     * 获取 channelId - 支付渠道ID.
     */
    public function getChannelId(): ?int
    {
        return $this->channelId;
    }

    /**
     * 赋值 channelId - 支付渠道ID.
     *
     * @param int|null $channelId channel_id
     *
     * @return static
     */
    public function setChannelId($channelId)
    {
        $this->channelId = null === $channelId ? null : (int) $channelId;

        return $this;
    }

    /**
     * 二级支付渠道ID.
     * secondary_channel_id.
     *
     * @Column(name="secondary_channel_id", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $secondaryChannelId = null;

    /**
     * 获取 secondaryChannelId - 二级支付渠道ID.
     */
    public function getSecondaryChannelId(): ?int
    {
        return $this->secondaryChannelId;
    }

    /**
     * 赋值 secondaryChannelId - 二级支付渠道ID.
     *
     * @param int|null $secondaryChannelId secondary_channel_id
     *
     * @return static
     */
    public function setSecondaryChannelId($secondaryChannelId)
    {
        $this->secondaryChannelId = null === $secondaryChannelId ? null : (int) $secondaryChannelId;

        return $this;
    }

    /**
     * 三级支付渠道ID.
     * tertiary_channel_id.
     *
     * @Column(name="tertiary_channel_id", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $tertiaryChannelId = null;

    /**
     * 获取 tertiaryChannelId - 三级支付渠道ID.
     */
    public function getTertiaryChannelId(): ?int
    {
        return $this->tertiaryChannelId;
    }

    /**
     * 赋值 tertiaryChannelId - 三级支付渠道ID.
     *
     * @param int|null $tertiaryChannelId tertiary_channel_id
     *
     * @return static
     */
    public function setTertiaryChannelId($tertiaryChannelId)
    {
        $this->tertiaryChannelId = null === $tertiaryChannelId ? null : (int) $tertiaryChannelId;

        return $this;
    }

    /**
     * 交易单号.
     * trade_no.
     *
     * @Column(name="trade_no", type="char", length=24, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $tradeNo = null;

    /**
     * 获取 tradeNo - 交易单号.
     */
    public function getTradeNo(): ?string
    {
        return $this->tradeNo;
    }

    /**
     * 赋值 tradeNo - 交易单号.
     *
     * @param string|null $tradeNo trade_no
     *
     * @return static
     */
    public function setTradeNo($tradeNo)
    {
        if (\is_string($tradeNo) && mb_strlen($tradeNo) > 24)
        {
            throw new \InvalidArgumentException('The maximum length of $tradeNo is 24');
        }
        $this->tradeNo = null === $tradeNo ? null : (string) $tradeNo;

        return $this;
    }

    /**
     * 支付渠道交易单号.
     * channel_trade_no.
     *
     * @Column(name="channel_trade_no", type="varchar", length=64, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $channelTradeNo = null;

    /**
     * 获取 channelTradeNo - 支付渠道交易单号.
     */
    public function getChannelTradeNo(): ?string
    {
        return $this->channelTradeNo;
    }

    /**
     * 赋值 channelTradeNo - 支付渠道交易单号.
     *
     * @param string|null $channelTradeNo channel_trade_no
     *
     * @return static
     */
    public function setChannelTradeNo($channelTradeNo)
    {
        if (\is_string($channelTradeNo) && mb_strlen($channelTradeNo) > 64)
        {
            throw new \InvalidArgumentException('The maximum length of $channelTradeNo is 64');
        }
        $this->channelTradeNo = null === $channelTradeNo ? null : (string) $channelTradeNo;

        return $this;
    }

    /**
     * 二级支付渠道交易单号.
     * secondary_trade_no.
     *
     * @Column(name="secondary_trade_no", type="varchar", length=64, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $secondaryTradeNo = null;

    /**
     * 获取 secondaryTradeNo - 二级支付渠道交易单号.
     */
    public function getSecondaryTradeNo(): ?string
    {
        return $this->secondaryTradeNo;
    }

    /**
     * 赋值 secondaryTradeNo - 二级支付渠道交易单号.
     *
     * @param string|null $secondaryTradeNo secondary_trade_no
     *
     * @return static
     */
    public function setSecondaryTradeNo($secondaryTradeNo)
    {
        if (\is_string($secondaryTradeNo) && mb_strlen($secondaryTradeNo) > 64)
        {
            throw new \InvalidArgumentException('The maximum length of $secondaryTradeNo is 64');
        }
        $this->secondaryTradeNo = null === $secondaryTradeNo ? null : (string) $secondaryTradeNo;

        return $this;
    }

    /**
     * 三级支付渠道交易单号.
     * tertiary_trade_no.
     *
     * @Column(name="tertiary_trade_no", type="varchar", length=64, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $tertiaryTradeNo = null;

    /**
     * 获取 tertiaryTradeNo - 三级支付渠道交易单号.
     */
    public function getTertiaryTradeNo(): ?string
    {
        return $this->tertiaryTradeNo;
    }

    /**
     * 赋值 tertiaryTradeNo - 三级支付渠道交易单号.
     *
     * @param string|null $tertiaryTradeNo tertiary_trade_no
     *
     * @return static
     */
    public function setTertiaryTradeNo($tertiaryTradeNo)
    {
        if (\is_string($tertiaryTradeNo) && mb_strlen($tertiaryTradeNo) > 64)
        {
            throw new \InvalidArgumentException('The maximum length of $tertiaryTradeNo is 64');
        }
        $this->tertiaryTradeNo = null === $tertiaryTradeNo ? null : (string) $tertiaryTradeNo;

        return $this;
    }

    /**
     * 业务类型.
     * business_type.
     *
     * @Column(name="business_type", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
     * 业务记录ID.
     * business_id.
     *
     * @Column(name="business_id", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $businessId = null;

    /**
     * 获取 businessId - 业务记录ID.
     */
    public function getBusinessId(): ?int
    {
        return $this->businessId;
    }

    /**
     * 赋值 businessId - 业务记录ID.
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
     * 用户ID.
     * member_id.
     *
     * @Column(name="member_id", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
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
     * 金额.
     * amount.
     *
     * @Column(name="amount", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?int $amount = null;

    /**
     * 获取 amount - 金额.
     */
    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * 赋值 amount - 金额.
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
     * @Column(name="left_amount", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
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
     * 备注.
     * remark.
     *
     * @Column(name="remark", type="text", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $remark = null;

    /**
     * 获取 remark - 备注.
     */
    public function getRemark(): ?string
    {
        return $this->remark;
    }

    /**
     * 赋值 remark - 备注.
     *
     * @param string|null $remark remark
     *
     * @return static
     */
    public function setRemark($remark)
    {
        if (\is_string($remark) && mb_strlen($remark) > 65535)
        {
            throw new \InvalidArgumentException('The maximum length of $remark is 65535');
        }
        $this->remark = null === $remark ? null : (string) $remark;

        return $this;
    }

    /**
     * 交易订单创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $createTime = null;

    /**
     * 获取 createTime - 交易订单创建时间.
     */
    public function getCreateTime(): ?int
    {
        return $this->createTime;
    }

    /**
     * 赋值 createTime - 交易订单创建时间.
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
     * 支付平台方返回的真实支付时间.
     * pay_time.
     *
     * @Column(name="pay_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $payTime = 0;

    /**
     * 获取 payTime - 支付平台方返回的真实支付时间.
     */
    public function getPayTime(): ?int
    {
        return $this->payTime;
    }

    /**
     * 赋值 payTime - 支付平台方返回的真实支付时间.
     *
     * @param int|null $payTime pay_time
     *
     * @return static
     */
    public function setPayTime($payTime)
    {
        $this->payTime = null === $payTime ? null : (int) $payTime;

        return $this;
    }

    /**
     * 我方接收到通知的时间.
     * notify_time.
     *
     * @Column(name="notify_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $notifyTime = 0;

    /**
     * 获取 notifyTime - 我方接收到通知的时间.
     */
    public function getNotifyTime(): ?int
    {
        return $this->notifyTime;
    }

    /**
     * 赋值 notifyTime - 我方接收到通知的时间.
     *
     * @param int|null $notifyTime notify_time
     *
     * @return static
     */
    public function setNotifyTime($notifyTime)
    {
        $this->notifyTime = null === $notifyTime ? null : (int) $notifyTime;

        return $this;
    }
}
