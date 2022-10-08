<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 11:10
 */

namespace Chance\Log\orm\illuminate;

use Chance\Log\OperationLog;
use Chance\Log\OperationLogInterface;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Log extends OperationLog implements OperationLogInterface
{
    /**
     * @param Model $model
     * @return string
     */
    public function getTableName($model): string
    {
        return $model->getConnection()->getTablePrefix() . $model->getTable();
    }

    /**
     * @param Model $model
     * @return string
     */
    public function getDatabaseName($model): string
    {
        return $model->getConnection()->getDatabaseName();
    }

    public function executeSQL(string $sql)
    {
        return Manager::select($sql);
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getAttributes($model): array
    {
        return $model->getAttributes();
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getChangedAttributes($model): array
    {
        $data = [];
        foreach ($model->getAttributes() as $key => $value) {
            if ($model->isClean($key)) {
                continue;
            }
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * @param Model $model
     * @param string $key
     * @return string
     */
    public function getValue($model, string $key): string
    {
        $keyText = $key . "_text";
        return $model->$keyText ?? $model->$key;
    }

    /**
     * @param Model $model
     * @param string $key
     * @return string
     */
    public function getOldValue($model, string $key): string
    {
        $keyText = $key . "_text";
        $attributeFun = "get" . Str::studly(Str::lower($keyText)) . "Attribute";
        return (string)(method_exists($model, $attributeFun) ? $model->$attributeFun($model->getOriginal($key)) : $model->getOriginal($key));
    }

    public function created($model)
    {
        $this->generateLog($model, self::CREATED);
    }

    public function updated($model)
    {
        $this->generateLog($model, self::UPDATED);
    }

    public function deleted($model)
    {
        $this->generateLog($model, self::DELETED);
    }

    public function batchCreated($model, array $data)
    {
        // TODO: Implement batchCreated() method.
    }

    public function batchUpdated($model, array $oldData, array $data)
    {
        // TODO: Implement batchUpdated() method.
    }

    public function batchDeleted($model, array $data)
    {
        // TODO: Implement batchDeleted() method.
    }
}