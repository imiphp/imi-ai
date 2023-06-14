<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Pgsql\Model\PgModel as Model;

/**
 * 训练的文件 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingFile.name", default="tb_embedding_file"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingFile.poolName", default="pgsql"))
 *
 * @property int|null    $id
 * @property int|null    $projectId            项目ID
 * @property int|null    $status               状态
 * @property string|null $fileName             文件名
 * @property int|null    $fileSize             文件大小，单位：字节
 * @property string|null $content              文件内容
 * @property int|null    $createTime           创建时间
 * @property int|null    $updateTime           更新时间
 * @property int|null    $beginTrainingTime    开始训练时间
 * @property int|null    $completeTrainingTime 完成训练时间
 * @property int|null    $tokens               Token数量
 */
abstract class EmbeddingFileBase extends Model
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
     * @Column(name="id", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=1, isAutoIncrement=true, ndims=0, virtual=false)
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
    public function setId(?int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * 项目ID.
     * project_id.
     *
     * @Column(name="project_id", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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
     * @Column(name="status", type="int2", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $status = null;

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
     * 文件名.
     * file_name.
     *
     * @Column(name="file_name", type="varchar", length=255, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $fileName = null;

    /**
     * 获取 fileName - 文件名.
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * 赋值 fileName - 文件名.
     *
     * @param string|null $fileName file_name
     *
     * @return static
     */
    public function setFileName(?string $fileName)
    {
        if (\is_string($fileName) && mb_strlen($fileName) > 255)
        {
            throw new \InvalidArgumentException('The maximum length of $fileName is 255');
        }
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * 文件大小，单位：字节.
     * file_size.
     *
     * @Column(name="file_size", type="int4", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $fileSize = null;

    /**
     * 获取 fileSize - 文件大小，单位：字节.
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * 赋值 fileSize - 文件大小，单位：字节.
     *
     * @param int|null $fileSize file_size
     *
     * @return static
     */
    public function setFileSize(?int $fileSize)
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    /**
     * 文件内容.
     * content.
     *
     * @Column(name="content", type="text", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $content = null;

    /**
     * 获取 content - 文件内容.
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * 赋值 content - 文件内容.
     *
     * @param string|null $content content
     *
     * @return static
     */
    public function setContent(?string $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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
    public function setCreateTime(?int $createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * 更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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
    public function setUpdateTime(?int $updateTime)
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    /**
     * 开始训练时间.
     * begin_training_time.
     *
     * @Column(name="begin_training_time", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $beginTrainingTime = 0;

    /**
     * 获取 beginTrainingTime - 开始训练时间.
     */
    public function getBeginTrainingTime(): ?int
    {
        return $this->beginTrainingTime;
    }

    /**
     * 赋值 beginTrainingTime - 开始训练时间.
     *
     * @param int|null $beginTrainingTime begin_training_time
     *
     * @return static
     */
    public function setBeginTrainingTime(?int $beginTrainingTime)
    {
        $this->beginTrainingTime = $beginTrainingTime;

        return $this;
    }

    /**
     * 完成训练时间.
     * complete_training_time.
     *
     * @Column(name="complete_training_time", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $completeTrainingTime = 0;

    /**
     * 获取 completeTrainingTime - 完成训练时间.
     */
    public function getCompleteTrainingTime(): ?int
    {
        return $this->completeTrainingTime;
    }

    /**
     * 赋值 completeTrainingTime - 完成训练时间.
     *
     * @param int|null $completeTrainingTime complete_training_time
     *
     * @return static
     */
    public function setCompleteTrainingTime(?int $completeTrainingTime)
    {
        $this->completeTrainingTime = $completeTrainingTime;

        return $this;
    }

    /**
     * Token数量.
     * tokens.
     *
     * @Column(name="tokens", type="int4", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $tokens = 0;

    /**
     * 获取 tokens - Token数量.
     */
    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    /**
     * 赋值 tokens - Token数量.
     *
     * @param int|null $tokens tokens
     *
     * @return static
     */
    public function setTokens(?int $tokens)
    {
        $this->tokens = $tokens;

        return $this;
    }
}
