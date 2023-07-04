<?php

declare(strict_types=1);

namespace app\Module\VCode\ApiController;

use app\Module\VCode\Service\VCodeService;
use Imi\Aop\Annotation\Inject;
use Imi\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/vcode/')]
class VCodeController extends HttpController
{
    #[Inject]
    protected VCodeService $vcodeService;

    #[Action]
    public function get(): array
    {
        $vcode = $this->vcodeService->generateVCode();

        return [
            'image' => base64_encode($vcode->getImage()),
            'token' => $vcode->getToken(),
        ];
    }
}
