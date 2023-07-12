<?php
/**
 * Created by PhpStorm
 * Date 2023/7/12 10:25.
 */

namespace Chance\Log\orm\hyperf\aspect;

use Chance\Log\orm\hyperf\Builder;
use Hyperf\Database\Query\Builder as QueryBuilder;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class NewBaseQueryBuilderAspect extends AbstractAspect
{
    public array $classes = [
        'Hyperf\Database\Model\Model::newBaseQueryBuilder',
    ];

    public function process(ProceedingJoinPoint $proceedingJoinPoint): Builder
    {
        /** @var QueryBuilder $query */
        $query = $proceedingJoinPoint->process();

        return new Builder($query->getConnection(), $query->getGrammar(), $query->getProcessor());
    }
}
