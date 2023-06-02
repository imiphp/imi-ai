<?php

declare(strict_types=1);

namespace app\ApiServer\Aop;

use Imi\Aop\AfterReturningJoinPoint;
use Imi\Aop\Annotation\AfterReturning;
use Imi\Aop\Annotation\Aspect;
use Imi\Aop\Annotation\PointCut;
use Imi\Aop\PointCutType;
use Imi\Server\Http\Route\Annotation\Action;
use Psr\Http\Message\ResponseInterface;

/**
 * @Aspect
 */
class ApiControllerAspect
{
    /**
     * @PointCut(
     *         type=PointCutType::ANNOTATION,
     *         allow={
     *             Action::class
     *         }
     * )
     *
     * @AfterReturning
     *
     * @return mixed
     */
    public function parse(AfterReturningJoinPoint $joinPoint)
    {
        $returnValue = $joinPoint->getReturnValue();
        if ($returnValue instanceof ResponseInterface)
        {
            return;
        }
        if (null === $returnValue || (\is_array($returnValue) && !isset($returnValue['code'])))
        {
            $returnValue['message'] = '';
            $returnValue['code'] = 0;
        }
        elseif (\is_object($returnValue) && !isset($returnValue->code))
        {
            $returnValue->message = '';
            $returnValue->code = 0;
        }
        else
        {
            return;
        }
        $joinPoint->setReturnValue($returnValue);
    }
}
