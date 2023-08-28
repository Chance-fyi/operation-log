<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 11:10.
 */

namespace Chance\Log\orm\illuminate;

use Chance\Log\OperationLog;
use Chance\Log\OperationLogInterface;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Log extends OperationLog implements OperationLogInterface
{
    /**
     * @param Model $model
     */
    public function getPk($model): string
    {
        return $model->getKeyName();
    }

    /**
     * @param Model $model
     */
    public function getTableName($model): string
    {
        return $model->getConnection()->getTablePrefix() . $model->getTable();
    }

    /**
     * @param Model $model
     */
    public function getDatabaseName($model): string
    {
        if (method_exists($model, 'getQuery')) {
            return $model->getQuery()->getDatabaseName();
        }

        return $model->getConnection()->getDatabaseName();
    }

    /**
     * @param Model $model
     */
    public function executeSQL($model, string $sql): array
    {
        if (method_exists($model, 'getQuery')) {
            return $model->getQuery()->select($sql);
        }

        return $model->getConnection()->select($sql);
    }

    /**
     * @param Model $model
     */
    public function getAttributes($model): array
    {
        return $model->getAttributes();
    }

    /**
     * @param Model $model
     */
    public function getChangedAttributes($model): array
    {
        return $model->getChanges();
    }

    /**
     * @param Model $model
     */
    public function getValue($model, string $key): string
    {
        $keyText = $key . '_text';
        $value = $model->{$keyText} ?? $model->{$key};

        if ($value instanceof ArrayObject) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * @param Model $model
     */
    public function getOldValue($model, string $key): string
    {
        if (str_contains($key, '->')) {
            [$key, $jsonKey] = explode('->', $key, 2);
        }

        $keyText = $key . '_text';
        $attributeFun = 'get' . Str::studly(Str::lower($keyText)) . 'Attribute';
        $value = (method_exists($model, $attributeFun) ? $model->{$attributeFun}($model->getOriginal($key)) : $model->getOriginal($key));

        if ($value instanceof ArrayObject) {
            return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
        }

        $val = json_decode($value, true);
        if (!isset($jsonKey) || is_null($val) || !is_array($val)) {
            return $value;
        }

        foreach (explode('->', $jsonKey) as $k) {
            $val = $val[$k];
        }

        return (string) $val;
    }

    /**
     * @param Model $model
     */
    public function created($model, array $data): void
    {
        $model->setRawAttributes($data);
        $this->generateLog($model, self::CREATED);
    }

    /**
     * @param Model $model
     */
    public function updated($model, array $oldData, array $data): void
    {
        $data = array_map(function ($value) {
            return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
        }, $data);

        $model->setRawAttributes($oldData, true);
        $model->setRawAttributes(array_merge($oldData, $data));
        $model->syncChanges();
        $this->generateLog($model, self::UPDATED);
    }

    /**
     * @param Model $model
     */
    public function deleted($model, array $data): void
    {
        $model->setRawAttributes($data);
        $this->generateLog($model, self::DELETED);
    }

    /**
     * @param Model $model
     */
    public function batchCreated($model, array $data): void
    {
        foreach ($data as $item) {
            $model->setRawAttributes($item);
            $this->generateLog($model, self::BATCH_CREATED);
        }
    }

    /**
     * @param Model $model
     */
    public function batchUpdated($model, array $oldData, array $data): void
    {
        foreach ($oldData as $item) {
            $model->setRawAttributes((array) $item, true);
            $model->setRawAttributes(array_merge((array) $item, $data));
            $model->syncChanges();
            $this->generateLog($model, self::BATCH_UPDATED);
        }
    }

    /**
     * @param Model $model
     */
    public function batchDeleted($model, array $data): void
    {
        foreach ($data as $item) {
            $model->setRawAttributes((array) $item);
            $this->generateLog($model, self::BATCH_DELETED);
        }
    }
}
