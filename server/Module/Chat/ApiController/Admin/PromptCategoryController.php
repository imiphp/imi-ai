<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Admin\Util\AdminMemberUtil;
use app\Module\Chat\Service\PromptCategoryService;
use app\Util\IPUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;
use Imi\Server\Http\Route\Annotation\Route;

#[Controller(prefix: '/admin/promptCategory/')]
class PromptCategoryController extends HttpController
{
    #[Inject()]
    protected PromptCategoryService $promptCategoryService;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function list(): array
    {
        return [
            'list' => $this->promptCategoryService->adminList(),
        ];
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: 'POST'),
        AdminLoginRequired()
    ]
    public function create(string $title, int $index = 128)
    {
        $this->promptCategoryService->create($title, $index, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: 'POST'),
        AdminLoginRequired()
    ]
    public function update(int $id, ?string $title = null, ?int $index = null)
    {
        $this->promptCategoryService->update($id, $title, $index, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }

    /**
     * @return mixed
     */
    #[
        Action,
        Route(method: 'POST'),
        AdminLoginRequired()
    ]
    public function delete(int $id)
    {
        $this->promptCategoryService->delete($id, AdminMemberUtil::getMemberSession()->getMemberId(), IPUtil::getIP());
    }
}
