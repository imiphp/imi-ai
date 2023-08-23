<?php

declare(strict_types=1);

namespace app\Module\Member\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use app\Module\Member\Enum\MemberStatus;
use app\Module\Member\Model\Member;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Consts\LogicalOperator;
use Imi\Db\Query\Where\Where;
use Imi\Db\Query\Where\WhereBrackets;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;
use Imi\Validate\ValidatorHelper;

class MemberService
{
    public const LOG_OBJECT = 'member';

    #[Inject]
    protected EmailAuthService $emailAuthService;

    #[Inject]
    protected AuthService $authService;

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

    #[
        AutoValidation(),
        Text(name: 'nickname', min: 1, message: '昵称不能为空', optional: true)
    ]
    public function update(int|string $id, ?string $nickname = null, ?string $email = null, ?string $password = null, ?int $status = null, int $operatorMemberId = 0, string $ip = ''): Member
    {
        $record = $this->get($id);
        if (null !== $nickname)
        {
            $record->nickname = $nickname;
        }
        if (null !== $email)
        {
            try
            {
                $tmpRecord = $this->getByEmail($email);
                if ($tmpRecord->id !== $record->id)
                {
                    throw new \Exception('邮箱已被使用');
                }
            }
            catch (NotFoundException $_)
            {
            }
            $record->email = $email;
            $record->emailHash = $this->emailAuthService->hash($email);
        }
        if (null !== $password)
        {
            $record->password = $this->authService->passwordHash($password);
        }
        if (null !== $status)
        {
            $record->status = $status;
        }
        $record->update();
        if ($operatorMemberId > 0)
        {
            OperationLog::log($operatorMemberId, self::LOG_OBJECT, OperationLogStatus::SUCCESS, sprintf('更新用户，id=%d, email=%s, phone=%s', $record->id, $record->email, $record->phone), $ip);
        }

        return $record;
    }

    public function list(string $search = '', int $status = 0, int $page = 1, int $limit = 15): array
    {
        $query = \app\Module\Member\Model\Admin\Member::query()->order('id', 'desc');
        if ('' !== $search)
        {
            $wheres = [];
            if (ValidatorHelper::email($search))
            {
                $wheres[] = new WhereBrackets(fn () => [
                    new Where('email_hash', '=', $this->emailAuthService->hash($search)),
                    new Where('email', '=', $search),
                ], LogicalOperator::OR);
            }
            if (ValidatorHelper::phone($search))
            {
                $wheres[] = new Where('phone', '=', (int) $search, LogicalOperator::OR);
            }
            $wheres[] = new Where('nickname', 'like', '%' . $search . '%', LogicalOperator::OR);
            $query->whereBrackets(static fn () => $wheres);
        }
        if ($status)
        {
            $query->where('status', '=', $status);
        }

        return $query->paginate($page, $limit)->toArray();
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
