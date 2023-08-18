<?php

declare(strict_types=1);

namespace app\Module\Member\Model;

use app\Module\Common\Model\Traits\TRecordId;
use app\Module\Member\Enum\MemberStatus;
use app\Module\Member\Model\Base\MemberBase;
use Hashids\Hashids;
use Imi\Bean\Annotation\Inherit;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Serializables;

/**
 * 用户.
 *
 * @Inherit
 */
#[Serializables(mode: 'deny', fields: ['id', 'emailHash', 'password', 'registerIpData', 'lastLoginIpData'])]
class Member extends MemberBase
{
    use TRecordId;

    /**
     * 注册时间.
     * register_time.
     *
     * @Column(name="register_time", type="int", length=10, accuracy=0, nullable=false, default="", isPrimaryKey=false, primaryKeyIndex=-1, isAutoIncrement=false, unsigned=true, virtual=false, createTime=true)
     */
    protected ?int $registerTime = null;

    /**
     * 状态
     */
    #[Column(virtual: true)]
    protected ?string $statusText = null;

    public function getStatusText(): ?string
    {
        return MemberStatus::getText($this->status);
    }

    public const INVITATION_ALPHABET = 'abcdefghijklmnopqrstuvwxyz1234567890';

    public static function encodeInvitationCode(int $code): string
    {
        return (new Hashids(self::__getSalt(), 8, self::INVITATION_ALPHABET))->encode($code);
    }

    public static function decodeInvitationCode(string $code): int
    {
        $result = (new Hashids(self::__getSalt(), 8, self::INVITATION_ALPHABET))->decode($code);
        if (!$result)
        {
            throw new \InvalidArgumentException('Invalid invitation code');
        }

        return $result[0];
    }

    /**
     * 邀请码
     */
    #[Column(virtual: true)]
    protected ?string $invitationCode = null;

    public function getInvitationCode(): ?string
    {
        if (null === $this->invitationCode && null !== $this->id)
        {
            return $this->invitationCode = self::encodeInvitationCode($this->id);
        }

        return $this->invitationCode;
    }
}
