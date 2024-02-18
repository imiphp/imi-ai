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
 * 支付渠道 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Payment\Model\PaymentChannel.name", default="tb_payment_channel"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Payment\Model\PaymentChannel.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_payment_channel` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `name` char(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',   PRIMARY KEY (`id`),   KEY `name` (`name`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='支付渠道'")
 *
 * @property int|null    $id
 * @property string|null $name 名称
 */
abstract class PaymentChannelBase extends Model
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
     * @Column(name="name", type="char", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
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
        if (\is_string($name) && mb_strlen($name) > 16)
        {
            throw new \InvalidArgumentException('The maximum length of $name is 16');
        }
        $this->name = null === $name ? null : (string) $name;

        return $this;
    }
}
