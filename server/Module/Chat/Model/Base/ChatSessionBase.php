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
 * AI聊天会话 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Chat\Model\ChatSession.name", default="tb_chat_session"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Chat\Model\ChatSession.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_chat_session` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `member_id` int(10) unsigned NOT NULL COMMENT '用户ID',   `title` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '标题',   `qa_status` tinyint(3) unsigned NOT NULL COMMENT '问答状态',   `tokens` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '累计使用 Token 数量',   `config` json NOT NULL COMMENT '配置',   `create_time` int(10) unsigned NOT NULL COMMENT '创建时间',   `update_time` int(10) unsigned NOT NULL COMMENT '最后更新时间',   `delete_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '删除时间',   PRIMARY KEY (`id`),   KEY `member_id` (`member_id`,`update_time`),   KEY `delete_time` (`delete_time`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC COMMENT='AI聊天会话'")
 *
 * @property int|null                                    $id
 * @property int|null                                    $memberId   用户ID
 * @property string|null                                 $title      标题
 * @property int|null                                    $qaStatus   问答状态
 * @property int|null                                    $tokens     累计使用 Token 数量
 * @property \Imi\Util\LazyArrayObject|object|array|null $config     配置
 * @property int|null                                    $createTime 创建时间
 * @property int|null                                    $updateTime 最后更新时间
 * @property int|null                                    $deleteTime 删除时间
 */
abstract class ChatSessionBase extends Model
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
     * @Column(name="id", type="bigint", length=20, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
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
     * 用户ID.
     * member_id.
     *
     * @Column(name="member_id", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
    public function setMemberId($memberId)
    {
        $this->memberId = null === $memberId ? null : (int) $memberId;

        return $this;
    }

    /**
     * 标题.
     * title.
     *
     * @Column(name="title", type="varchar", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
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
     * 问答状态.
     * qa_status.
     *
     * @Column(name="qa_status", type="tinyint", length=3, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $qaStatus = null;

    /**
     * 获取 qaStatus - 问答状态.
     */
    public function getQaStatus(): ?int
    {
        return $this->qaStatus;
    }

    /**
     * 赋值 qaStatus - 问答状态.
     *
     * @param int|null $qaStatus qa_status
     *
     * @return static
     */
    public function setQaStatus($qaStatus)
    {
        $this->qaStatus = null === $qaStatus ? null : (int) $qaStatus;

        return $this;
    }

    /**
     * 累计使用 Token 数量.
     * tokens.
     *
     * @Column(name="tokens", type="int", length=10, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $tokens = 0;

    /**
     * 获取 tokens - 累计使用 Token 数量.
     */
    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    /**
     * 赋值 tokens - 累计使用 Token 数量.
     *
     * @param int|null $tokens tokens
     *
     * @return static
     */
    public function setTokens($tokens)
    {
        $this->tokens = null === $tokens ? null : (int) $tokens;

        return $this;
    }

    /**
     * 配置.
     * config.
     *
     * @Column(name="config", type="json", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     *
     * @var \Imi\Util\LazyArrayObject|object|array|null
     */
    protected $config = null;

    /**
     * 获取 config - 配置.
     *
     * @return \Imi\Util\LazyArrayObject|object|array|null
     */
    public function &getConfig()
    {
        return $this->config;
    }

    /**
     * 赋值 config - 配置.
     *
     * @param \Imi\Util\LazyArrayObject|object|array|null $config config
     *
     * @return static
     */
    public function setConfig($config)
    {
        $this->config = null === $config ? null : $config;

        return $this;
    }

    /**
     * 创建时间.
     * create_time.
     *
     * @Column(name="create_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
     * 最后更新时间.
     * update_time.
     *
     * @Column(name="update_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $updateTime = null;

    /**
     * 获取 updateTime - 最后更新时间.
     */
    public function getUpdateTime(): ?int
    {
        return $this->updateTime;
    }

    /**
     * 赋值 updateTime - 最后更新时间.
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

    /**
     * 删除时间.
     * delete_time.
     *
     * @Column(name="delete_time", type="int", length=10, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $deleteTime = 0;

    /**
     * 获取 deleteTime - 删除时间.
     */
    public function getDeleteTime(): ?int
    {
        return $this->deleteTime;
    }

    /**
     * 赋值 deleteTime - 删除时间.
     *
     * @param int|null $deleteTime delete_time
     *
     * @return static
     */
    public function setDeleteTime($deleteTime)
    {
        $this->deleteTime = null === $deleteTime ? null : (int) $deleteTime;

        return $this;
    }
}
