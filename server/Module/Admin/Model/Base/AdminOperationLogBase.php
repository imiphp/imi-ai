<?php

declare(strict_types=1);

namespace app\Module\Admin\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 后台操作日志 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Admin\Model\AdminOperationLog.name", default="tb_admin_operation_log"), usePrefix=false, id={"id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Admin\Model\AdminOperationLog.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_admin_operation_log` (   `id` bigint unsigned NOT NULL AUTO_INCREMENT,   `member_id` int unsigned NOT NULL COMMENT '后台用户ID',   `object` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作对象',   `status` tinyint unsigned NOT NULL COMMENT '状态',   `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消息内容',   `ip_data` varbinary(16) NOT NULL DEFAULT '' COMMENT '最后登录IP数据',   `ip` varchar(39) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci GENERATED ALWAYS AS ((case length(`ip_data`) when 0 then _utf8mb4'' else inet6_ntoa(`ip_data`) end)) VIRTUAL NOT NULL COMMENT '最后登录IP',   `time` bigint unsigned NOT NULL COMMENT '毫秒时间戳',   PRIMARY KEY (`id`),   KEY `member_id` (`member_id`,`object`,`status`,`time`) USING BTREE ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPRESSED COMMENT='后台操作日志'")
 *
 * @property int|null    $id
 * @property int|null    $memberId 后台用户ID
 * @property string|null $object   操作对象
 * @property int|null    $status   状态
 * @property string|null $message  消息内容
 * @property string|null $ipData   最后登录IP数据
 * @property string|null $ip       最后登录IP
 * @property int|null    $time     毫秒时间戳
 */
abstract class AdminOperationLogBase extends Model
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
     * @Column(name="id", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=true, unsigned=true, virtual=false)
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
     * 后台用户ID.
     * member_id.
     *
     * @Column(name="member_id", type="int", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $memberId = null;

    /**
     * 获取 memberId - 后台用户ID.
     */
    public function getMemberId(): ?int
    {
        return $this->memberId;
    }

    /**
     * 赋值 memberId - 后台用户ID.
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
     * 操作对象.
     * object.
     *
     * @Column(name="object", type="varchar", length=32, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $object = null;

    /**
     * 获取 object - 操作对象.
     */
    public function getObject(): ?string
    {
        return $this->object;
    }

    /**
     * 赋值 object - 操作对象.
     *
     * @param string|null $object object
     *
     * @return static
     */
    public function setObject($object)
    {
        if (\is_string($object) && mb_strlen($object) > 32)
        {
            throw new \InvalidArgumentException('The maximum length of $object is 32');
        }
        $this->object = null === $object ? null : (string) $object;

        return $this;
    }

    /**
     * 状态.
     * status.
     *
     * @Column(name="status", type="tinyint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
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
    public function setStatus($status)
    {
        $this->status = null === $status ? null : (int) $status;

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
     * 最后登录IP数据.
     * ip_data.
     *
     * @Column(name="ip_data", type="varbinary", length=16, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?string $ipData = '';

    /**
     * 获取 ipData - 最后登录IP数据.
     */
    public function getIpData(): ?string
    {
        return $this->ipData;
    }

    /**
     * 赋值 ipData - 最后登录IP数据.
     *
     * @param string|null $ipData ip_data
     *
     * @return static
     */
    public function setIpData($ipData)
    {
        $this->ipData = null === $ipData ? null : (string) $ipData;

        return $this;
    }

    /**
     * 最后登录IP.
     * ip.
     *
     * @Column(name="ip", type="varchar", length=39, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=true)
     */
    protected ?string $ip = null;

    /**
     * 获取 ip - 最后登录IP.
     */
    public function getIp(): ?string
    {
        return $this->ip;
    }

    /**
     * 赋值 ip - 最后登录IP.
     *
     * @param string|null $ip ip
     *
     * @return static
     */
    public function setIp($ip)
    {
        if (\is_string($ip) && mb_strlen($ip) > 39)
        {
            throw new \InvalidArgumentException('The maximum length of $ip is 39');
        }
        $this->ip = null === $ip ? null : (string) $ip;

        return $this;
    }

    /**
     * 毫秒时间戳.
     * time.
     *
     * @Column(name="time", type="bigint", length=0, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $time = null;

    /**
     * 获取 time - 毫秒时间戳.
     */
    public function getTime(): ?int
    {
        return $this->time;
    }

    /**
     * 赋值 time - 毫秒时间戳.
     *
     * @param int|null $time time
     *
     * @return static
     */
    public function setTime($time)
    {
        $this->time = null === $time ? null : (int) $time;

        return $this;
    }
}
