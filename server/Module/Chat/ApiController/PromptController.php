<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController;

use app\Module\Chat\Service\PromptService;
use app\Util\RequestUtil;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/chat/prompt/')]
class PromptController extends HttpController
{
    #[Inject()]
    protected PromptService $promptService;

    #[Action()]
    public function list(string|array $categoryIds = [], string $search = '', int $page = 1, int $limit = 15): array
    {
        return $this->promptService->list(RequestUtil::parseArrayParams($categoryIds), $search, $page, $limit);
    }

    #[Action()]
    public function get(string $id): array
    {
        return [
            'data' => $this->promptService->get($id),
        ];
    }
}
