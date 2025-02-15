<?php

declare(strict_types=1);

namespace app\Module\Admin\Service;

use app\Exception\NotFoundException;
use app\Module\Admin\Enum\AdminMemberStatus;
use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Model\AdminMember;
use app\Module\Admin\Util\OperationLog;
use Imi\Aop\Annotation\Inject;
use Imi\Db\Annotation\Transaction;
use Imi\Db\Mysql\Consts\LogicalOperator;
use Imi\Db\Query\Where\Where;
use Imi\Util\Text as UtilText;
use Imi\Validate\Annotation\AutoValidation;
use Imi\Validate\Annotation\Text;

class AdminMemberService
{
    public const LOG_OBJECT = OperationLogObject::ADMIN_MEMBER;

    #[Inject()]
    protected AdminAuthService $authService;

    #[
        Transaction(),
        AutoValidation(),
        Text(name: 'account', min: 1, message: '账号不能为空'),
        Text(name: 'password', min: 1, message: '密码不能为空'),
    ]
    public function create(string $account, string $password, string $nickname, int $status = AdminMemberStatus::NORMAL, int $operatorMemberId = 0, string $ip = ''): AdminMember
    {
        try
        {
            $this->getByAccount($account);
            throw new \RuntimeException('账号已存在');
        }
        catch (NotFoundException $_)
        {
            $record = AdminMember::newInstance();
            $record->account = $account;
            $record->status = $status;
            $record->password = $this->authService->passwordHash($password);
            if ('' === $nickname)
            {
                $nickname = $account;
            }
            $record->nickname = $nickname;
            $record->insert();
            OperationLog::log($operatorMemberId, self::LOG_OBJECT, OperationLogStatus::SUCCESS, sprintf('创建后台用户，id=%d, account=%s', $record->id, $record->account), $ip);

            return $record;
        }
    }

    #[
        AutoValidation(),
        Text(name: 'account', min: 1, message: '账号不能为空'),
        Text(name: 'nickname', min: 1, message: '昵称不能为空')
    ]
    public function update(int $id, ?string $account = null, ?string $password = null, ?string $nickname = null, ?int $status = null, int $operatorMemberId = 0, string $ip = ''): AdminMember
    {
        try
        {
            $record = $this->getByAccount($account);
            if ($record->id !== $id)
            {
                throw new \RuntimeException('账号已存在');
            }
        }
        catch (NotFoundException $_)
        {
            $record = $this->get($id);
        }
        if (null !== $account)
        {
            $record->account = $account;
        }
        if (!UtilText::isEmpty($password))
        {
            $record->password = $this->authService->passwordHash($password);
        }
        if (null !== $nickname)
        {
            $record->nickname = $nickname;
        }
        if (null !== $status)
        {
            $record->status = $status;
        }
        $record->update();
        OperationLog::log($operatorMemberId, self::LOG_OBJECT, OperationLogStatus::SUCCESS, sprintf('更新后台用户，id=%d, account=%s', $record->id, $record->account), $ip);

        return $record;
    }

    public function delete(int $id, int $operatorMemberId = 0, string $ip = ''): void
    {
        $record = $this->get($id);
        $record->delete();
        OperationLog::log($operatorMemberId, self::LOG_OBJECT, OperationLogStatus::SUCCESS, sprintf('删除后台用户，id=%d, account=%s', $record->id, $record->account), $ip);
    }

    public function get(int $id): AdminMember
    {
        $record = AdminMember::find($id);
        if (!$record)
        {
            throw new NotFoundException('用户不存在');
        }

        return $record;
    }

    public function getByAccount(string $account): AdminMember
    {
        $record = AdminMember::find([
            'account' => $account,
        ]);
        if (!$record)
        {
            throw new NotFoundException('用户不存在');
        }

        return $record;
    }

    public function list(string $search = '', int $status = 0, int $page = 1, int $limit = 15): array
    {
        $query = AdminMember::query()->order('id', 'desc');
        if ('' !== $search)
        {
            $query->whereBrackets(fn () => [
                new Where('account', 'like', '%' . $search . '%'),
                new Where('nickname', 'like', '%' . $search . '%', LogicalOperator::OR),
            ]);
        }
        if ($status)
        {
            $query->where('status', '=', $status);
        }

        return $query->paginate($page, $limit)->toArray();
    }
}
