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
 * 提示语分类 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Chat\Model\PromptCategory.name", default="tb_prompt_category"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Chat\Model\PromptCategory.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_prompt_category` (   `id` int unsigned NOT NULL AUTO_INCREMENT,   `title` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '标题',   `index` tinyint unsigned NOT NULL DEFAULT '128' COMMENT '排序，0-255，最小越靠前',   `create_time` int unsigned NOT NULL COMMENT '创建时间',   `update_time` int unsigned NOT NULL COMMENT '更新时间',   PRIMARY KEY (`id`) USING BTREE,   KEY `index` (`index`,`update_time` DESC) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='提示语分类'")
 *
 * @property int|null    $id
 * @property string|null $title      标题
 * @property int|null    $index      排序，0-255，最小越靠前
 * @property int|null    $createTime 创建时间
 * @property int|null    $updateTime 更新时间
 */
abstract class PromptCategoryBase extends Model
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
     * 标题.
     * title.
     *
     * @Column(name="title", type="varchar", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $title = null;

    /**
     * 获取 title - 标题.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * 赋值 title - 标题.
     *
     * @param string|null $title title
     *
     * @return static
     */
    public function setTitle($title)
    {
        if (\is_string($title) && mb_strlen($title) > 16)
        {
            throw new \InvalidArgumentException('The maximum length of $title is 16');
        }
        $this->title = null === $title ? null : (string) $title;

        return $this;
    }

    /**
     * 排序，0-255，最小越靠前.
     * index.
     *
     * @Column(name="index", type="tinyint", length=0, accuracy=0, nullable=false, default="128", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $index = 128;

    /**
     * 获取 index - 排序，0-255，最小越靠前.
     */
    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * 赋值 index - 排序，0-255，最小越靠前.
     *
     * @param int|null $index index
     *
     * @return static
     */
    public function setIndex($index)
    {
        $this->index = null === $index ? null : (int) $index;

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
     * 更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $updateTime = null;

    /**
     * 获取 updateTime - 更新时间.
     */
    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    /**
     * 赋值 updateTime - 更新时间.
     *
     * @param int|null $updateTime update_time
     *
     * @return static
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = null === $updateTime ? null : (int) $updateTime;

        return $this;
    }
}
