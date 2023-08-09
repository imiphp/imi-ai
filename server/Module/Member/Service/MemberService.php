<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\NotFoundException;
use app\Module\Member\Enum\MemberStatus;
use app\Module\Member\Model\Member;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Validate\ValidatorHelper;

class MemberService
{
    #[Inject]
    protected EmailAuthService $emailAuthService;

    #[Transaction()]
    public function create(string $email = '', int $phone = 0, string $password = '', string $nickname = '', int $status = MemberStatus::NORMAL, string $ip = ''): Member
    {
        $record = Member::newInstance();
        $record->status = $status;
        $record->email = $email;
        $record->emailHash = $this->emailAuthService->hash($email);
        $record->phone = $phone;
        $record->password = $password;
        $record->nickname = $nickname;
        $record->registerIpData = inet_pton($ip) ?: '';
        $record->insert();

        return $record;
    }

    public function update(int|string $id, string $nickname): Member
    {
        $record = $this->get($id);
        $record->nickname = $nickname;
        $record->update();

        return $record;
    }

    public function get(int|string $id): Member
    {
        $record = Member::find(\is_int($id) ? $id : Member::decodeId($id));
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

    /**
     * @return int[]
     */
    public function queryIdsBySearch(string $queryString): array
    {
        $query = Member::query();
        if (ValidatorHelper::int($queryString))
        {
            if (ValidatorHelper::phone($queryString))
            {
                $query->where('phone', '=', (int) $queryString);
            }
            else
            {
                return [(int) $queryString];
            }
        }
        elseif (ValidatorHelper::email($queryString))
        {
            $query->where('email_hash', '=', $this->emailAuthService->hash($queryString))
                  ->where('email', '=', $queryString);
        }

        return $query->column('id');
    }
}
