<?php

declare(strict_types=1);

namespace app\Module\Chat\ApiController\Admin;

use app\Module\Admin\Annotation\AdminLoginRequired;
use app\Module\Chat\Prompt\PromptCrawler;
use Imi\Aop\Annotation\Inject;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/admin/prompt/')]
class PromptController extends HttpController
{
    #[Inject()]
    protected PromptCrawler $promptCrawler;

    #[
        Action,
        AdminLoginRequired()
    ]
    public function crawlerList(): array
    {
        return [
            'list' => $this->promptCrawler->getCrawlers(),
        ];
    }
}
