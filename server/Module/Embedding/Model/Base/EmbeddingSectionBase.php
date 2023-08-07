<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Pgsql\Model\PgModel as Model;

/**
 * 训练内容段落 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingSection.name", default="tb_embedding_section"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingSection.poolName", default="pgsql"))
 *
 * @property int|null    $id
 * @property int|null    $projectId            项目ID
 * @property int|null    $fileId               文件ID
 * @property int|null    $status               状态
 * @property string|null $content              文件内容
 * @property string|null $vector               向量
 * @property int|null    $createTime           创建时间
 * @property int|null    $updateTime           更新时间
 * @property int|null    $beginTrainingTime    开始训练时间
 * @property int|null    $completeTrainingTime 完成训练时间
 * @property string|null $reason               失败原因
 * @property int|null    $tokens               Token数量
 * @property int|null    $payTokens            支付 Token 数量
 * @property string|null $title                标题
 * @property int|null    $contentPosition      内容位置
 * @property int|null    $contentLength        内容长度
 */
abstract class EmbeddingSectionBase extends Model
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
     * 文件ID.
     * file_id.
     *
     * @Column(name="file_id", type="int8", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $fileId = null;

    /**
     * 获取 fileId - 文件ID.
     */
    public function getFileId(): ?int
    {
        return $this->fileId;
    }

    /**
     * 赋值 fileId - 文件ID.
     *
     * @param int|null $fileId file_id
     *
     * @return static
     */
    public function setFileId(?int $fileId)
    {
        $this->fileId = $fileId;

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
     * 向量.
     * vector.
     *
     * @Column(name="vector", type="vector", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $vector = null;

    /**
     * 获取 vector - 向量.
     */
    public function getVector(): ?string
    {
        return $this->vector;
    }

    /**
     * 赋值 vector - 向量.
     *
     * @param string|null $vector vector
     *
     * @return static
     */
    public function setVector(?string $vector)
    {
        $this->vector = $vector;

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
     * 失败原因.
     * reason.
     *
     * @Column(name="reason", type="text", length=-1, accuracy=0, nullable=false, default="''::text", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $reason = '';

    /**
     * 获取 reason - 失败原因.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * 赋值 reason - 失败原因.
     *
     * @param string|null $reason reason
     *
     * @return static
     */
    public function setReason(?string $reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Token数量.
     * tokens.
     *
     * @Column(name="tokens", type="int4", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $tokens = null;

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

    /**
     * 支付 Token 数量.
     * pay_tokens.
     *
     * @Column(name="pay_tokens", type="int4", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $payTokens = 0;

    /**
     * 获取 payTokens - 支付 Token 数量.
     */
    public function getPayTokens(): ?int
    {
        return $this->payTokens;
    }

    /**
     * 赋值 payTokens - 支付 Token 数量.
     *
     * @param int|null $payTokens pay_tokens
     *
     * @return static
     */
    public function setPayTokens(?int $payTokens)
    {
        $this->payTokens = $payTokens;

        return $this;
    }

    /**
     * 标题.
     * title.
     *
     * @Column(name="title", type="text", length=-1, accuracy=0, nullable=false, default="''::text", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $title = '';

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
    public function setTitle(?string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * 内容位置.
     * content_position.
     *
     * @Column(name="content_position", type="int4", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $contentPosition = 0;

    /**
     * 获取 contentPosition - 内容位置.
     */
    public function getContentPosition(): ?int
    {
        return $this->contentPosition;
    }

    /**
     * 赋值 contentPosition - 内容位置.
     *
     * @param int|null $contentPosition content_position
     *
     * @return static
     */
    public function setContentPosition(?int $contentPosition)
    {
        $this->contentPosition = $contentPosition;

        return $this;
    }

    /**
     * 内容长度.
     * content_length.
     *
     * @Column(name="content_length", type="int4", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $contentLength = 0;

    /**
     * 获取 contentLength - 内容长度.
     */
    public function getContentLength(): ?int
    {
        return $this->contentLength;
    }

    /**
     * 赋值 contentLength - 内容长度.
     *
     * @param int|null $contentLength content_length
     *
     * @return static
     */
    public function setContentLength(?int $contentLength)
    {
        $this->contentLength = $contentLength;

        return $this;
    }
}
