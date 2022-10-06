<?php
/**
 * Created by PhpStorm
 * Date 2022/9/28 17:15
 */

namespace Chance\Log\facades;

use Chance\Log\Facade;

/**
 * @method static getLog()
 * @method static clear()
 */
class OperationLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \Chance\Log\OperationLog::class;
    }
}