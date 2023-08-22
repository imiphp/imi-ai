<?php

declare(strict_types=1);

namespace app\Module\Embedding\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Pgsql\Model\PgModel as Model;

/**
 * 文件训练项目 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingProject.name", default="tb_embedding_project"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Embedding\Model\EmbeddingProject.poolName", default="pgsql"))
 *
 * @property int|null                             $id
 * @property int|null                             $memberId            用户ID
 * @property string|null                          $name                项目名称
 * @property int|null                             $totalFileSize       文件总大小，单位：字节
 * @property int|null                             $createTime          创建时间
 * @property int|null                             $updateTime          更新时间
 * @property int|null                             $status              状态
 * @property int|null                             $tokens              Token数量
 * @property int|null                             $payTokens           支付 Token 数量
 * @property string|null                          $ip                  IP地址
 * @property bool|null                            $public              是否公开使用
 * @property string|null                          $sectionSeparator    段落分隔符
 * @property int|null                             $sectionSplitLength  段落分割长度
 * @property bool|null                            $sectionSplitByTitle 使用标题分割段落
 * @property \Imi\Util\LazyArrayObject|array|null $chatConfig          聊天对话推荐配置
 */
abstract class EmbeddingProjectBase extends Model
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
     * 项目名称.
     * name.
     *
     * @Column(name="name", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $name = null;

    /**
     * 获取 name - 项目名称.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * 赋值 name - 项目名称.
     *
     * @param string|null $name name
     *
     * @return static
     */
    public function setName(?string $name)
    {
        if (\is_string($name) && mb_strlen($name) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $name is 32');
        }
        $this->name = $name;

        return $this;
    }

    /**
     * 文件总大小，单位：字节.
     * total_file_size.
     *
     * @Column(name="total_file_size", type="int4", length=-1, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $totalFileSize = null;

    /**
     * 获取 totalFileSize - 文件总大小，单位：字节.
     */
    public function getTotalFileSize(): ?int
    {
        return $this->totalFileSize;
    }

    /**
     * 赋值 totalFileSize - 文件总大小，单位：字节.
     *
     * @param int|null $totalFileSize total_file_size
     *
     * @return static
     */
    public function setTotalFileSize(?int $totalFileSize)
    {
        $this->totalFileSize = $totalFileSize;

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
     * Token数量.
     * tokens.
     *
     * @Column(name="tokens", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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

    /**
     * 支付 Token 数量.
     * pay_tokens.
     *
     * @Column(name="pay_tokens", type="int8", length=-1, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
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

    /**
     * 是否公开使用.
     * public.
     *
     * @Column(name="public", type="bool", length=-1, accuracy=0, nullable=false, default="false", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?bool $public = false;

    /**
     * 获取 public - 是否公开使用.
     */
    public function getPublic(): ?bool
    {
        return $this->public;
    }

    /**
     * 赋值 public - 是否公开使用.
     *
     * @param bool|null $public public
     *
     * @return static
     */
    public function setPublic(?bool $public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     * 段落分隔符.
     * section_separator.
     *
     * @Column(name="section_separator", type="varchar", length=16, accuracy=0, nullable=false, default="''::character varying", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?string $sectionSeparator = '';

    /**
     * 获取 sectionSeparator - 段落分隔符.
     */
    public function getSectionSeparator(): ?string
    {
        return $this->sectionSeparator;
    }

    /**
     * 赋值 sectionSeparator - 段落分隔符.
     *
     * @param string|null $sectionSeparator section_separator
     *
     * @return static
     */
    public function setSectionSeparator(?string $sectionSeparator)
    {
        if (\is_string($sectionSeparator) && mb_strlen($sectionSeparator) > 16)
        {
            throw new \InvalidArgumentException('The maximum length of $sectionSeparator is 16');
        }
        $this->sectionSeparator = $sectionSeparator;

        return $this;
    }

    /**
     * 段落分割长度.
     * section_split_length.
     *
     * @Column(name="section_split_length", type="int4", length=-1, accuracy=0, nullable=false, default="512", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?int $sectionSplitLength = 512;

    /**
     * 获取 sectionSplitLength - 段落分割长度.
     */
    public function getSectionSplitLength(): ?int
    {
        return $this->sectionSplitLength;
    }

    /**
     * 赋值 sectionSplitLength - 段落分割长度.
     *
     * @param int|null $sectionSplitLength section_split_length
     *
     * @return static
     */
    public function setSectionSplitLength(?int $sectionSplitLength)
    {
        $this->sectionSplitLength = $sectionSplitLength;

        return $this;
    }

    /**
     * 使用标题分割段落.
     * section_split_by_title.
     *
     * @Column(name="section_split_by_title", type="bool", length=-1, accuracy=0, nullable=false, default="true", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     */
    protected ?bool $sectionSplitByTitle = true;

    /**
     * 获取 sectionSplitByTitle - 使用标题分割段落.
     */
    public function getSectionSplitByTitle(): ?bool
    {
        return $this->sectionSplitByTitle;
    }

    /**
     * 赋值 sectionSplitByTitle - 使用标题分割段落.
     *
     * @param bool|null $sectionSplitByTitle section_split_by_title
     *
     * @return static
     */
    public function setSectionSplitByTitle(?bool $sectionSplitByTitle)
    {
        $this->sectionSplitByTitle = $sectionSplitByTitle;

        return $this;
    }

    /**
     * 聊天对话推荐配置.
     * chat_config.
     *
     * @Column(name="chat_config", type="json", length=-1, accuracy=0, nullable=false, default="'{}'::json", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, ndims=0, virtual=false)
     *
     * @var \Imi\Util\LazyArrayObject|array|null
     */
    protected $chatConfig = null;

    /**
     * 获取 chatConfig - 聊天对话推荐配置.
     *
     * @return \Imi\Util\LazyArrayObject|array|null
     */
    public function &getChatConfig()
    {
        return $this->chatConfig;
    }

    /**
     * 赋值 chatConfig - 聊天对话推荐配置.
     *
     * @param \Imi\Util\LazyArrayObject|array|null $chatConfig chat_config
     *
     * @return static
     */
    public function setChatConfig($chatConfig)
    {
        $this->chatConfig = $chatConfig;

        return $this;
    }
}
