<?php

declare(strict_types=1);

namespace app\Module\Chat\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 提示语采集来源 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Chat\Model\PromptCrawlerOrigin.name", default="tb_prompt_crawler_origin"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Chat\Model\PromptCrawlerOrigin.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_prompt_crawler_origin` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类名',   PRIMARY KEY (`id`) USING BTREE,   KEY `class` (`class`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='提示语采集来源'")
 *
 * @property int|null    $id
 * @property string|null $class 类名
 */
abstract class PromptCrawlerOriginBase extends Model
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
     * 类名.
     * class.
     *
     * @Column(name="class", type="varchar", length=255, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $class = null;

    /**
     * 获取 class - 类名.
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * 赋值 class - 类名.
     *
     * @param string|null $class class
     *
     * @return static
     */
    public function setClass($class)
    {
        if (\is_string($class) && mb_strlen($class) > 255)
        {
            throw new \InvalidArgumentException('The maximum length of $class is 255');
        }
        $this->class = null === $class ? null : (string) $class;

        return $this;
    }
}
