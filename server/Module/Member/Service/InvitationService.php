<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\NotFoundException;
use app\Module\Business\Enum\BusinessType;
use app\Module\Card\Service\MemberCardService;
use app\Module\Member\Model\Member;
use app\Module\Member\Model\Redis\MemberConfig;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;

class InvitationService
{
    #[Inject()]
    protected MemberService $memberService;

    #[Inject()]
    protected MemberCardService $memberCardService;

    #[Transaction()]
    public function bind(int $memberId, string $invitationCode): void
    {
        $config = MemberConfig::__getConfig();
        if (!$config->getEnableInvitation())
        {
            throw new \RuntimeException('邀请功能未开启');
        }
        if (!$config->getEnableInputInvitation())
        {
            throw new \RuntimeException('邀请码输入功能未开启');
        }
        $inviteeMember = $this->memberService->get($memberId);
        if ($inviteeMember->inviterId > 0)
        {
            throw new \RuntimeException('请不要重复输入邀请码');
        }
        $inviterMember = $this->getMemberByInvitationCode($invitationCode);
        if ($inviterMember->id === $inviteeMember->id)
        {
            throw new \RuntimeException('不能输入自己的邀请码');
        }
        $inviteeMember->inviterId = $inviterMember->id;
        $inviteeMember->inviterTime = time();
        $inviteeMember->update();

        // 邀请人
        $this->memberCardService->giftMemberBaseCard($inviterMember->id, $config->getInviterGiftAmount(), BusinessType::INVITER);

        // 被邀请人
        $this->memberCardService->giftMemberBaseCard($inviteeMember->id, $config->getInviteeGiftAmount(), BusinessType::INVITEE);
    }

    public function getMemberByInvitationCode(string $invitationCode): Member
    {
        try
        {
            $id = Member::decodeInvitationCode($invitationCode);
        }
        catch (\InvalidArgumentException $_)
        {
            throw new NotFoundException('邀请码不存在');
        }
        $record = Member::find($id);
        if (!$record)
        {
            throw new NotFoundException('邀请码不存在');
        }

        return $record;
    }
}
