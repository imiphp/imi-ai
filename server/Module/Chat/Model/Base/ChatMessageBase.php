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
 * AI聊天对话消息 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Chat\Model\ChatMessage.name", default="tb_chat_message"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Chat\Model\ChatMessage.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_chat_message` (   `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,   `session_id` bigint(20) unsigned NOT NULL COMMENT '会话ID',   `role` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色',   `config` json NOT NULL COMMENT '配置',   `tokens` int(10) unsigned NOT NULL COMMENT 'token数量',   `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消息内容',   `begin_time` int(10) unsigned NOT NULL COMMENT '开始时间',   `complete_time` int(10) unsigned NOT NULL COMMENT '完成时间',   PRIMARY KEY (`id`),   KEY `session_id` (`session_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='AI聊天对话消息'")
 *
 * @property int|null                                    $id
 * @property int|null                                    $sessionId    会话ID
 * @property string|null                                 $role         角色
 * @property \Imi\Util\LazyArrayObject|object|array|null $config       配置
 * @property int|null                                    $tokens       token数量
 * @property string|null                                 $message      消息内容
 * @property int|null                                    $beginTime    开始时间
 * @property int|null                                    $completeTime 完成时间
 */
abstract class ChatMessageBase extends Model
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
     * 会话ID.
     * session_id.
     *
     * @Column(name="session_id", type="bigint", length=20, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $sessionId = null;

    /**
     * 获取 sessionId - 会话ID.
     */
    public function getSessionId(): ?int
    {
        return $this->sessionId;
    }

    /**
     * 赋值 sessionId - 会话ID.
     *
     * @param int|null $sessionId session_id
     *
     * @return static
     */
    public function setSessionId($sessionId)
    {
        $this->sessionId = null === $sessionId ? null : (int) $sessionId;

        return $this;
    }

    /**
     * 角色.
     * role.
     *
     * @Column(name="role", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $role = null;

    /**
     * 获取 role - 角色.
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * 赋值 role - 角色.
     *
     * @param string|null $role role
     *
     * @return static
     */
    public function setRole($role)
    {
        if (\is_string($role) && mb_strlen($role) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $role is 32');
        }
        $this->role = null === $role ? null : (string) $role;

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
     * token数量.
     * tokens.
     *
     * @Column(name="tokens", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $tokens = null;

    /**
     * 获取 tokens - token数量.
     */
    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    /**
     * 赋值 tokens - token数量.
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
     * 消息内容.
     * message.
     *
     * @Column(name="message", type="text", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $message = null;

    /**
     * 获取 message - 消息内容.
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * 赋值 message - 消息内容.
     *
     * @param string|null $message message
     *
     * @return static
     */
    public function setMessage($message)
    {
        if (\is_string($message) && mb_strlen($message) > 65535)
        {
            throw new \InvalidArgumentException('The maximum length of $message is 65535');
        }
        $this->message = null === $message ? null : (string) $message;

        return $this;
    }

    /**
     * 开始时间.
     * begin_time.
     *
     * @Column(name="begin_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $beginTime = null;

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
    public function setBeginTime($beginTime)
    {
        $this->beginTime = null === $beginTime ? null : (int) $beginTime;

        return $this;
    }

    /**
     * 完成时间.
     * complete_time.
     *
     * @Column(name="complete_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $completeTime = null;

    /**
     * 获取 completeTime - 完成时间.
     */
    public function getCompleteTime(): ?int
    {
        return $this->completeTime;
    }

    /**
     * 赋值 completeTime - 完成时间.
     *
     * @param int|null $completeTime complete_time
     *
     * @return static
     */
    public function setCompleteTime($completeTime)
    {
        $this->completeTime = null === $completeTime ? null : (int) $completeTime;

        return $this;
    }
}
