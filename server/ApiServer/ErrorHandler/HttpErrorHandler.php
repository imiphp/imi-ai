<?php

declare(strict_types=1);

namespace app\ApiServer\ErrorHandler;

use app\Enum\ApiStatus;
use app\Exception\BaseException;
use Imi\App;
use Imi\RequestContext;
use Imi\Server\Http\Error\IErrorHandler;
use Imi\Server\View\Annotation\View;

class HttpErrorHandler implements IErrorHandler
{
    protected View $viewAnnotation;

    public function __construct()
    {
        $this->viewAnnotation = new View();
    }

    public function handle(\Throwable $throwable): bool
    {
        $cancelThrow = false;
        if ($throwable instanceof BaseException)
        {
            $code = $throwable->getStatusCode();
        }
        else
        {
            $code = ApiStatus::ERROR;
        }
        $data = [
            'code'    => $code,
            'message' => $throwable->getMessage(),
        ];
        if (App::isDebug())
        {
            $data['exception'] = [
                'message'   => $throwable->getMessage(),
                'code'      => $throwable->getCode(),
                'file'      => $throwable->getFile(),
                'line'      => $throwable->getLine(),
                'trace'     => explode(\PHP_EOL, $throwable->getTraceAsString()),
            ];
        }
        $requestContext = RequestContext::getContext();
        /** @var \Imi\Server\View\Handler\Json $jsonView */
        $jsonView = $requestContext['server']->getBean('JsonView');
        $jsonView->handle($this->viewAnnotation, null, $data, $requestContext['response'] ?? null)->send();

        return $cancelThrow;
    }
}
