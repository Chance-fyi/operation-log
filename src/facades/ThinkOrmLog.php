<?php
/**
 * Created by PhpStorm
 * Date 2022/10/6 15:14
 */

namespace Chance\Log\facades;

use Chance\Log\Facade;
use Chance\Log\orm\think\Log;
use think\Model;

/**
 * @method static created(Model $model, array $data)
 * @method static updated(Model $model, array $oldData, array $data)
 * @method static deleted(Model $model, array $data)
 * @method static batchCreated(Model $param, array $data)
 * @method static batchUpdated(Model $model, $oldData, array $data)
 * @method static batchDeleted(Model $model, array $data)
 * @method static beginTransaction()
 * @method static rollBackTransaction()
 */
class ThinkOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Log::class;
    }
}