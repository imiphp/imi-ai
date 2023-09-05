<?php

declare(strict_types=1);

namespace app\Module\Email\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Email\Service\EmailBlackListService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;
use Imi\Util\Http\Consts\RequestMethod;

#[Controller(prefix: '/admin/email/blackList/')]
class EmailBlackListController extends HttpController
{
    #[Inject()]
    protected EmailBlackListService $service;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(string $search = '', int $page = 1, int $limit = 15): array
    {
        return $this->service->list($search, $page, $limit);
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function add(array $domains)
    {
        $this->service->add($domains, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: RequestMethod::POST),
        AdminLoginRequired()
    ]
    public function remove(array $domains)
    {
        $this->service->remove($domains, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }
}
