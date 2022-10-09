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
     * DateTime: 2022/10/8 10:58
     * @param Model $model
     * @return string
     */
    public function getPk($model): string
    {
        return $model->getKeyName();
    }

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
        if (method_exists($model, "getQuery")) {
            return $model->getQuery()->getDatabaseName();
        }
        return $model->getConnection()->getDatabaseName();
    }

    /**
     * DateTime: 2022/10/9 13:15
     * @param Model $model
     * @param string $sql
     * @return array
     */
    public function executeSQL($model, string $sql): array
    {
        if (method_exists($model, "getQuery")) {
            return $model->getQuery()->select($sql);
        }
        return $model->getConnection()->select($sql);
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
        return $model->getChanges();
    }

    /**
     * @param Model $model
     * @param string $key
     * @return string
     */
    public function getValue($model, string $key): string
    {
        $keyText = $key . "_text";
        return (string)($model->$keyText ?? $model->$key);
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

    /**
     * DateTime: 2022/10/8 11:22
     * @param Model $model
     * @param array $data
     */
    public function created($model, array $data)
    {
        foreach ($data as $key => $value) {
            $model->setAttribute($key, $value);
        }
        $this->generateLog($model, self::CREATED);
    }

    /**
     * DateTime: 2022/10/8 11:22
     * @param Model $model
     * @param array $oldData
     * @param array $data
     */
    public function updated($model, array $oldData, array $data)
    {
        foreach ($oldData as $key => $value) {
            $model->setAttribute($key, $value);
        }
        $model->syncOriginal();
        foreach ($data as $key => $value) {
            $model->setAttribute($key, $value);
        }
        $model->syncChanges();
        $this->generateLog($model, self::UPDATED);
    }

    /**
     * DateTime: 2022/10/8 11:22
     * @param Model $model
     * @param array $data
     */
    public function deleted($model, array $data)
    {
        foreach ($data as $key => $value) {
            $model->setAttribute($key, $value);
        }
        $this->generateLog($model, self::DELETED);
    }

    /**
     * DateTime: 2022/10/8 11:22
     * @param Model $model
     * @param array $data
     */
    public function batchCreated($model, array $data)
    {
        foreach ($data as $item) {
            foreach ($item as $key => $value) {
                $model->setAttribute($key, $value);
            }
            $this->generateLog($model, self::BATCH_CREATED);
        }
    }

    /**
     * DateTime: 2022/10/8 11:22
     * @param Model $model
     * @param array $oldData
     * @param array $data
     */
    public function batchUpdated($model, array $oldData, array $data)
    {
        foreach ($oldData as $item) {
            foreach ((array)$item as $key => $value) {
                $model->setAttribute($key, $value);
            }
            $model->syncOriginal();
            foreach ($data as $key => $value) {
                $model->setAttribute($key, $value);
            }
            $model->syncChanges();
            $this->generateLog($model, self::BATCH_UPDATED);
        }
    }

    /**
     * DateTime: 2022/10/8 11:22
     * @param Model $model
     * @param array $data
     */
    public function batchDeleted($model, array $data)
    {
        foreach ($data as $item) {
            foreach ((array)$item as $key => $value) {
                $model->setAttribute($key, $value);
            }
            $this->generateLog($model, self::BATCH_DELETED);
        }
    }
}