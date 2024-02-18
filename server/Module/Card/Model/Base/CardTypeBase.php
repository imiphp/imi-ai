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
 * @DDL(sql="CREATE TABLE `tb_card_type` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',   `amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '初始余额',   `expire_seconds` int unsigned NOT NULL COMMENT '激活后增加的有效时长，单位：秒',   `enable` bit(1) NOT NULL COMMENT '是否启用',   `system` bit(1) NOT NULL DEFAULT b'0' COMMENT '系统内置',   `member_activation_limit` int unsigned NOT NULL DEFAULT '0' COMMENT '每个用户最多激活次数，0代表不限制',   `sale_price` int unsigned NOT NULL DEFAULT '0' COMMENT '售价，单位：分',   `sale_actual_price` int unsigned NOT NULL DEFAULT '0' COMMENT '实际售价，单位：分',   `sale_enable` bit(1) NOT NULL DEFAULT b'0' COMMENT '是否允许销售',   `sale_index` tinyint unsigned NOT NULL DEFAULT '127' COMMENT '销售排序，0-255，越小越靠前',   `sale_begin_time` int unsigned NOT NULL DEFAULT '0' COMMENT '销售开始时间',   `sale_end_time` int unsigned NOT NULL DEFAULT '0' COMMENT '销售结束时间',   `sale_limit_quantity` int unsigned NOT NULL DEFAULT '0' COMMENT '销售数量限制，0表示不限制',   `sale_paying` bit(1) NOT NULL DEFAULT b'0' COMMENT '销售购买后是否为付费标识',   `create_time` int unsigned NOT NULL COMMENT '创建时间',   PRIMARY KEY (`id`) USING BTREE,   KEY `status` (`enable`,`create_time` DESC) USING BTREE,   KEY `enable` (`enable`,`sale_enable`,`sale_index`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='卡类型'")
 *
 * @property int|null    $id
 * @property string|null $name                  名称
 * @property int|null    $amount                初始余额
 * @property int|null    $expireSeconds         激活后增加的有效时长，单位：秒
 * @property bool|null   $enable                是否启用
 * @property bool|null   $system                系统内置
 * @property int|null    $memberActivationLimit 每个用户最多激活次数，0代表不限制
 * @property int|null    $salePrice             售价，单位：分
 * @property int|null    $saleActualPrice       实际售价，单位：分
 * @property bool|null   $saleEnable            是否允许销售
 * @property int|null    $saleIndex             销售排序，0-255，越小越靠前
 * @property int|null    $saleBeginTime         销售开始时间
 * @property int|null    $saleEndTime           销售结束时间
 * @property int|null    $saleLimitQuantity     销售数量限制，0表示不限制
 * @property bool|null   $salePaying            销售购买后是否为付费标识
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
     * 售价，单位：分.
     * sale_price.
     *
     * @Column(name="sale_price", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $salePrice = 0;

    /**
     * 获取 salePrice - 售价，单位：分.
     */
    public function getSalePrice(): ?int
    {
        return $this->salePrice;
    }

    /**
     * 赋值 salePrice - 售价，单位：分.
     *
     * @param int|null $salePrice sale_price
     *
     * @return static
     */
    public function setSalePrice($salePrice)
    {
        $this->salePrice = null === $salePrice ? null : (int) $salePrice;

        return $this;
    }

    /**
     * 实际售价，单位：分.
     * sale_actual_price.
     *
     * @Column(name="sale_actual_price", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $saleActualPrice = 0;

    /**
     * 获取 saleActualPrice - 实际售价，单位：分.
     */
    public function getSaleActualPrice(): ?int
    {
        return $this->saleActualPrice;
    }

    /**
     * 赋值 saleActualPrice - 实际售价，单位：分.
     *
     * @param int|null $saleActualPrice sale_actual_price
     *
     * @return static
     */
    public function setSaleActualPrice($saleActualPrice)
    {
        $this->saleActualPrice = null === $saleActualPrice ? null : (int) $saleActualPrice;

        return $this;
    }

    /**
     * 是否允许销售.
     * sale_enable.
     *
     * @Column(name="sale_enable", type="bit", length=1, accuracy=0, nullable=false, default="b'0'", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?bool $saleEnable = false;

    /**
     * 获取 saleEnable - 是否允许销售.
     */
    public function getSaleEnable(): ?bool
    {
        return $this->saleEnable;
    }

    /**
     * 赋值 saleEnable - 是否允许销售.
     *
     * @param bool|null $saleEnable sale_enable
     *
     * @return static
     */
    public function setSaleEnable($saleEnable)
    {
        $this->saleEnable = null === $saleEnable ? null : (bool) $saleEnable;

        return $this;
    }

    /**
     * 销售排序，0-255，越小越靠前.
     * sale_index.
     *
     * @Column(name="sale_index", type="tinyint", length=0, accuracy=0, nullable=false, default="127", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $saleIndex = 127;

    /**
     * 获取 saleIndex - 销售排序，0-255，越小越靠前.
     */
    public function getSaleIndex(): ?int
    {
        return $this->saleIndex;
    }

    /**
     * 赋值 saleIndex - 销售排序，0-255，越小越靠前.
     *
     * @param int|null $saleIndex sale_index
     *
     * @return static
     */
    public function setSaleIndex($saleIndex)
    {
        $this->saleIndex = null === $saleIndex ? null : (int) $saleIndex;

        return $this;
    }

    /**
     * 销售开始时间.
     * sale_begin_time.
     *
     * @Column(name="sale_begin_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $saleBeginTime = 0;

    /**
     * 获取 saleBeginTime - 销售开始时间.
     */
    public function getSaleBeginTime(): ?int
    {
        return $this->saleBeginTime;
    }

    /**
     * 赋值 saleBeginTime - 销售开始时间.
     *
     * @param int|null $saleBeginTime sale_begin_time
     *
     * @return static
     */
    public function setSaleBeginTime($saleBeginTime)
    {
        $this->saleBeginTime = null === $saleBeginTime ? null : (int) $saleBeginTime;

        return $this;
    }

    /**
     * 销售结束时间.
     * sale_end_time.
     *
     * @Column(name="sale_end_time", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $saleEndTime = 0;

    /**
     * 获取 saleEndTime - 销售结束时间.
     */
    public function getSaleEndTime(): ?int
    {
        return $this->saleEndTime;
    }

    /**
     * 赋值 saleEndTime - 销售结束时间.
     *
     * @param int|null $saleEndTime sale_end_time
     *
     * @return static
     */
    public function setSaleEndTime($saleEndTime)
    {
        $this->saleEndTime = null === $saleEndTime ? null : (int) $saleEndTime;

        return $this;
    }

    /**
     * 销售数量限制，0表示不限制.
     * sale_limit_quantity.
     *
     * @Column(name="sale_limit_quantity", type="int", length=0, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $saleLimitQuantity = 0;

    /**
     * 获取 saleLimitQuantity - 销售数量限制，0表示不限制.
     */
    public function getSaleLimitQuantity(): ?int
    {
        return $this->saleLimitQuantity;
    }

    /**
     * 赋值 saleLimitQuantity - 销售数量限制，0表示不限制.
     *
     * @param int|null $saleLimitQuantity sale_limit_quantity
     *
     * @return static
     */
    public function setSaleLimitQuantity($saleLimitQuantity)
    {
        $this->saleLimitQuantity = null === $saleLimitQuantity ? null : (int) $saleLimitQuantity;

        return $this;
    }

    /**
     * 销售购买后是否为付费标识.
     * sale_paying.
     *
     * @Column(name="sale_paying", type="bit", length=1, accuracy=0, nullable=false, default="b'0'", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?bool $salePaying = false;

    /**
     * 获取 salePaying - 销售购买后是否为付费标识.
     */
    public function getSalePaying(): ?bool
    {
        return $this->salePaying;
    }

    /**
     * 赋值 salePaying - 销售购买后是否为付费标识.
     *
     * @param bool|null $salePaying sale_paying
     *
     * @return static
     */
    public function setSalePaying($salePaying)
    {
        $this->salePaying = null === $salePaying ? null : (bool) $salePaying;

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
