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
 * @method static batchCreated(Model $param, array $data)
 * @method static batchUpdated(Model $model, $oldData, array $data)
 * @method static batchDeleted(Model $model, array $data)
 */
class ThinkOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return \Chance\Log\orm\ThinkOrmLog::class;
    }
}