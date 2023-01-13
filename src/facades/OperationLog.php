<?php
/**
 * Created by PhpStorm
 * Date 2022/9/28 17:15
 */

namespace Chance\Log\facades;

use Chance\Log\Facade;

/**
 * @method static getLog()
 * @method static clearLog()
 * @method static beginTransaction()
 * @method static rollBackTransaction()
 * @method static setTableModelMapping(array $map)
 * @method static getTableModelMapping()
 */
class OperationLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \Chance\Log\OperationLog::class;
    }
}