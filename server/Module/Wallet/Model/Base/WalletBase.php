<?php

declare(strict_types=1);

namespace app\Module\Wallet\Model\Base;

use Imi\Config\Annotation\ConfigValue;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\DDL;
use Imi\Model\Annotation\Entity;
use Imi\Model\Annotation\Table;
use Imi\Model\Model;

/**
 * 用户钱包 基类.
 *
 * @Entity(camel=true, bean=true, incrUpdate=true)
 *
 * @Table(name=@ConfigValue(name="@app.models.app\Module\Wallet\Model\Wallet.name", default="tb_wallet"), usePrefix=false, id={"member_id"}, dbPoolName=@ConfigValue(name="@app.models.app\Module\Wallet\Model\Wallet.poolName"))
 *
 * @DDL(sql="CREATE TABLE `tb_wallet` (   `member_id` int(10) unsigned NOT NULL,   `tokens` bigint(20) NOT NULL DEFAULT '0' COMMENT 'Tokens 余额',   PRIMARY KEY (`member_id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT COMMENT='用户钱包'")
 *
 * @property int|null $memberId
 * @property int|null $tokens   Tokens 余额
 */
abstract class WalletBase extends Model
{
    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEY = 'member_id';

    /**
     * {@inheritdoc}
     */
    public const PRIMARY_KEYS = ['member_id'];

    /**
     * member_id.
     *
     * @Column(name="member_id", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=true, primaryKeyIndex=0, isAutoIncrement=false, unsigned=true, virtual=false)
     */
    protected ?int $memberId = null;

    /**
     * 获取 memberId.
     */
    public function getMemberId(): ?int
    {
        return $this->memberId;
    }

    /**
     * 赋值 memberId.
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
     * Tokens 余额.
     * tokens.
     *
     * @Column(name="tokens", type="bigint", length=20, accuracy=0, nullable=false, default="0", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=false, virtual=false)
     */
    protected ?int $tokens = 0;

    /**
     * 获取 tokens - Tokens 余额.
     */
    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    /**
     * 赋值 tokens - Tokens 余额.
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
}
