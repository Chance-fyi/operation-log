<?php
/**
 * Created by PhpStorm
 * Date 2022/10/6 15:15.
 */

namespace Chance\Log\facades;

use Chance\Log\Facade;
use Chance\Log\orm\hyperf\Log;
use Hyperf\Database\Model\Model;

/**
 * @mixin Log
 *
 * @method static created(Model $model, array $data)
 * @method static updated(Model $model, array $oldData, array $data)
 * @method static deleted(Model $model, array $data)
 * @method static batchCreated(Model $model, array $data)
 * @method static batchUpdated(Model $model, array $oldData, array $data)
 * @method static batchDeleted(Model $model, array $data)
 * @method static beginTransaction()
 * @method static rollBackTransaction(int $toLevel)
 * @method static status()
 */
class HyperfOrmLog extends Facade
{
    protected static function getFacadeClass(): string
    {
        return Log::class;
    }
}
