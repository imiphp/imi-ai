<?php

declare(strict_types=1);

namespace app\Module\Member\Model\Traits;

use app\Module\Member\Model\Member;
use Imi\Model\Annotation\Column;
use Imi\Model\Annotation\Relation\JoinFrom;
use Imi\Model\Annotation\Relation\JoinTo;
use Imi\Model\Annotation\Relation\OneToOne;
use Imi\Model\Annotation\Serializable;
use Imi\Model\Enum\RelationPoolName;

trait TMemberInfo
{
    #[
        OneToOne(model: Member::class, with: true, poolName: RelationPoolName::RELATION),
        JoinFrom(field: 'member_id'),
        JoinTo(field: 'id'),
        Serializable(allow: false)
    ]
    protected ?Member $member = null;

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    #[Column(virtual: true)]
    protected ?array $memberInfo = null;

    public function getMemberInfo(): ?array
    {
        if (null === $this->memberInfo)
        {
            return $this->memberInfo = [
                'recordId' => $this->member->getRecordId(),
                'nickname' => $this->member->nickname,
            ];
        }

        return $this->memberInfo;
    }
}
