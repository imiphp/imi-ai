<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\NotFoundException;
use app\Module\Member\Model\Member;
use Imi\Aop\Annotation\Inject;

class MemberService
{
    #[Inject]
    protected EmailAuthService $emailAuthService;

    public function create(string $email = '', int $phone = 0, string $password = '', string $nickname = ''): Member
    {
        $record = Member::newInstance();
        $record->email = $email;
        $record->emailHash = $this->emailAuthService->hash($email);
        $record->phone = $phone;
        $record->password = $password;
        $record->nickname = $nickname;
        $record->insert();

        return $record;
    }

    public function get(int $id): Member
    {
        $record = Member::find($id);
        if (!$record)
        {
            throw new NotFoundException('用户不存在');
        }

        return $record;
    }

    public function getByEmail(string $email): Member
    {
        $record = Member::find([
            'email_hash' => $this->emailAuthService->hash($email),
            'email'      => $email,
        ]);
        if (!$record)
        {
            throw new NotFoundException('用户不存在');
        }

        return $record;
    }

    public function getByPhone(int $phone): Member
    {
        if ($phone <= 0)
        {
            throw new NotFoundException('用户不存在');
        }
        $record = Member::find([
            'phone' => $phone,
        ]);
        if (!$record)
        {
            throw new NotFoundException('用户不存在');
        }

        return $record;
    }
}
