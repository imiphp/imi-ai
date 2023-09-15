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
 * 卡扩展数据 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Card\Model\CardEx.name", default="tb_card_ex"), usePrefix=false, id={"card_id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Card\Model\CardEx.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_card_ex` (   `card_id` int unsigned NOT NULL,   `admin_remark` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '后台备注',   PRIMARY KEY (`card_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='卡扩展数据'")
 *
 * @property int|null    $cardId
 * @property string|null $adminRemark 后台备注
 */
abstract class CardExBase extends Model
{
    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEY = 'card_id';

    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEYS = ['card_id'];

    /**
     * card_id.
     *
     * @Column(name="card_id", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $cardId = null;

    /**
     * 获取 cardId.
     */
    public function getCardId(): ?int
    {
        return $this->cardId;
    }

    /**
     * 赋值 cardId.
     *
     * @param int|null $cardId card_id
     *
     * @return static
     */
    public function setCardId($cardId)
    {
        $this->cardId = null === $cardId ? null : (int) $cardId;

        return $this;
    }

    /**
     * 后台备注.
     * admin_remark.
     *
     * @Column(name="admin_remark", type="text", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $adminRemark = null;

    /**
     * 获取 adminRemark - 后台备注.
     */
    public function getAdminRemark(): ?string
    {
        return $this->adminRemark;
    }

    /**
     * 赋值 adminRemark - 后台备注.
     *
     * @param string|null $adminRemark admin_remark
     *
     * @return static
     */
    public function setAdminRemark($adminRemark)
    {
        if (\is_string($adminRemark) && mb_strlen($adminRemark) > 65535)
        {
            throw new \InvalidArgumentException('The maximum length of $adminRemark is 65535');
        }
        $this->adminRemark = null === $adminRemark ? null : (string) $adminRemark;

        return $this;
    }
}
