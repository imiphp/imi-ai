<?php

declare(strict_types=1);

namespace app\Module\Email\Service;

use app\Module\Admin\Enum\OperationLogObject;
use app\Module\Admin\Enum\OperationLogStatus;
use app\Module\Admin\Util\OperationLog;
use Imi\Email\BlackList\Util\EmailBlackListUtil;

class EmailBlackListService
{
    public function list(string $search = '', int $page = 1, int $limit = 15): array
    {
        $handler = EmailBlackListUtil::getHandler();

        return [
            'list'  => $handler->list($search, $page, $limit),
            'total' => $handler->count(),
        ];
    }

    public function add(array $domains, int $operatorMemberId = 0, string $ip = ''): void
    {
        EmailBlackListUtil::getHandler()->add($domains);

        OperationLog::log($operatorMemberId, OperationLogObject::EMAIL_DOMAIN_BLACK_LIST, OperationLogStatus::SUCCESS, '添加邮箱域名黑名单', $ip);
    }

    public function remove(array $domains, int $operatorMemberId = 0, string $ip = ''): void
    {
        EmailBlackListUtil::getHandler()->remove($domains);

        OperationLog::log($operatorMemberId, OperationLogObject::EMAIL_DOMAIN_BLACK_LIST, OperationLogStatus::SUCCESS, '删除邮箱域名黑名单', $ip);
    }
}
