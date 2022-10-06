<?php
/**
 * Created by PhpStorm
 * Date 2022/10/6 15:14
 */

namespace Chance\Log\facades;

use Chance\Log\Facade;

/**
 * @method static created($model)
 * @method static updated($model)
 * @method static deleted($model)
 */
class ThinkOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \Chance\Log\orm\ThinkOrmLog::class;
    }
}