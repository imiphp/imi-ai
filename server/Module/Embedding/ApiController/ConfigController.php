<?php

declare(strict_types=1);

namespace app\Module\Embedding\ApiController;

use app\Module\Embedding\Enum\CompressFileTypes;
use app\Module\Embedding\Enum\ContentFileTypes;
use Imi\Server\Http\Controller\HttpController;
use Imi\Server\Http\Route\Annotation\Action;
use Imi\Server\Http\Route\Annotation\Controller;

#[Controller(prefix: '/embedding/config/')]
class ConfigController extends HttpController
{
    #[Action]
    public function fileTypes(): array
    {
        return [
            'compressFileTypes' => CompressFileTypes::getValues(),
            'contentFileTypes'  => ContentFileTypes::getValues(),
        ];
    }
}
