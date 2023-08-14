<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController;

use app\Module\Chat\Service\PromptCategoryService;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/chat/promptCategory/')]
class PromptCategoryController extends HttpController
{
    #[Inject()]
    protected PromptCategoryService $promptCategoryService;

    #[Action()]
    public function list(): array
    {
        return [
            'list' => $this->promptCategoryService->list(),
        ];
    }
}
