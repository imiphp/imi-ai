<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Pgsql\Model\PgModel as Model;

/**
 * 公共项目 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingPublicProject.name", default="tb_embedding_public_project"), usePrefix=false, id={"project_id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingPublicProject.poolName", default="pgsql"))
 *
 * @property int|null $projectId 项目ID
 * @property int|null $status    状态
 * @property int|null $time      时间
 * @property int|null $index     排序，越小越靠前
 */
abstract class EmbeddingPublicProjectBase extends Model
{
    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEY = 'project_id';

    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEYS = ['project_id'];

    /**
     * 项目ID.
     * project_id.
     *
     * @Column(name="project_id", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $projectId = null;

    /**
     * 获取 projectId - 项目ID.
     */
    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    /**
     * 赋值 projectId - 项目ID.
     *
     * @param int|null $projectId project_id
     *
     * @return static
     */
    public function setProjectId(?int $projectId)
    {
        $this->projectId = $projectId;

        return $this;
    }

    /**
     * 状态.
     * status.
     *
     * @Column(name="status", type="int2", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $status = 0;

    /**
     * 获取 status - 状态.
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * 赋值 status - 状态.
     *
     * @param int|null $status status
     *
     * @return static
     */
    public function setStatus(?int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * 时间.
     * time.
     *
     * @Column(name="time", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $time = 0;

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
    public function setTime(?int $time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * 排序，越小越靠前.
     * index.
     *
     * @Column(name="index", type="int2", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $index = null;

    /**
     * 获取 index - 排序，越小越靠前.
     */
    public function getIndex(): ?int
    {
        return $this->index;
    }

    /**
     * 赋值 index - 排序，越小越靠前.
     *
     * @param int|null $index index
     *
     * @return static
     */
    public function setIndex(?int $index)
    {
        $this->index = $index;

        return $this;
    }
}
