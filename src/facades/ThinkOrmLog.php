<?php
/**
 * Created by PhpStorm
 * Date 2022/10/6 15:14
 */

namespace Chance\Log\facades;

use Chance\Log\Facade;
use think\Model;

/**
 * @method static created($model)
 * @method static updated($model)
 * @method static deleted($model)
 * @method static insert(Model $param, array $data)
 * @method static insertAll(Model $param, array $dataSet)
 * @method static update($model, $oldData, array $data)
 */
class ThinkOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \Chance\Log\orm\ThinkOrmLog::class;
    }
}