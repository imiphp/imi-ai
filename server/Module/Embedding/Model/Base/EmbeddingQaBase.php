<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Pgsql\Model\PgModel as Model;

/**
 * 训练文件问答 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingQa.name", default="tb_embedding_qa"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingQa.poolName", default="pgsql"))
 *
 * @property int|null                             $id
 * @property int|null                             $memberId     用户ID
 * @property int|null                             $projectId    项目ID
 * @property string|null                          $question     问题
 * @property string|null                          $answer       回答
 * @property int|null                             $beginTime    开始时间
 * @property int|null                             $completeTime 结束时间
 * @property int|null                             $tokens       总tokens
 * @property \Imi\Util\LazyArrayObject|array|null $config       配置
 * @property int|null                             $status       状态
 * @property string|null                          $title        标题
 * @property int|null                             $createTime   创建时间
 * @property int|null                             $payTokens    支付 Token 数量
 * @property string|null                          $ip           IP地址
 */
abstract class EmbeddingQaBase extends Model
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
     * 用户ID.
     * member_id.
     *
     * @Column(name="member_id", type="int4", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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
    public function setMemberId(?int $memberId)
    {
        $this->memberId = $memberId;

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
     * 问题.
     * question.
     *
     * @Column(name="question", type="text", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $question = null;

    /**
     * 获取 question - 问题.
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * 赋值 question - 问题.
     *
     * @param string|null $question question
     *
     * @return static
     */
    public function setQuestion(?string $question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * 回答.
     * answer.
     *
     * @Column(name="answer", type="text", length=-1, accuracy=0, nullable=false, default="''::text", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $answer = '';

    /**
     * 获取 answer - 回答.
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * 赋值 answer - 回答.
     *
     * @param string|null $answer answer
     *
     * @return static
     */
    public function setAnswer(?string $answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * 开始时间.
     * begin_time.
     *
     * @Column(name="begin_time", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $beginTime = 0;

    /**
     * 获取 beginTime - 开始时间.
     */
    public function getBeginTime(): ?int
    {
        return $this->beginTime;
    }

    /**
     * 赋值 beginTime - 开始时间.
     *
     * @param int|null $beginTime begin_time
     *
     * @return static
     */
    public function setBeginTime(?int $beginTime)
    {
        $this->beginTime = $beginTime;

        return $this;
    }

    /**
     * 结束时间.
     * complete_time.
     *
     * @Column(name="complete_time", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $completeTime = 0;

    /**
     * 获取 completeTime - 结束时间.
     */
    public function getCompleteTime(): ?int
    {
        return $this->completeTime;
    }

    /**
     * 赋值 completeTime - 结束时间.
     *
     * @param int|null $completeTime complete_time
     *
     * @return static
     */
    public function setCompleteTime(?int $completeTime)
    {
        $this->completeTime = $completeTime;

        return $this;
    }

    /**
     * 总tokens.
     * tokens.
     *
     * @Column(name="tokens", type="int4", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $tokens = 0;

    /**
     * 获取 tokens - 总tokens.
     */
    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    /**
     * 赋值 tokens - 总tokens.
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
     * 配置.
     * config.
     *
     * @Column(name="config", type="json", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     *
     * @var \Imi\Util\LazyArrayObject|array|null
     */
    protected $config = null;

    /**
     * 获取 config - 配置.
     *
     * @return \Imi\Util\LazyArrayObject|array|null
     */
    public function &getConfig()
    {
        return $this->config;
    }

    /**
     * 赋值 config - 配置.
     *
     * @param \Imi\Util\LazyArrayObject|array|null $config config
     *
     * @return static
     */
    public function setConfig($config)
    {
        $this->config = $config;

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
     * 标题.
     * title.
     *
     * @Column(name="title", type="varchar", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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
    public function setTitle(?string $title)
    {
        if (\is_string($title) && mb_strlen($title) > 16)
        {
            throw new \InvalidArgumentException('The maximum length of $title is 16');
        }
        $this->title = $title;

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
     * IP地址.
     * ip.
     *
     * @Column(name="ip", type="inet", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $ip = null;

    /**
     * 获取 ip - IP地址.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * 赋值 ip - IP地址.
     *
     * @param string|null $ip ip
     *
     * @return static
     */
    public function setIp(?string $ip)
    {
        $this->ip = $ip;

        return $this;
    }
}
