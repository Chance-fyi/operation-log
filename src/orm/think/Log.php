<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:18.
 */

namespace Chance\Log\orm\think;

use Chance\Log\OperationLog;
use Chance\Log\OperationLogInterface;
use think\db\exception\DbException;
use think\db\PDOConnection;
use think\db\Raw;
use think\helper\Str;
use think\Model;

class Log extends OperationLog implements OperationLogInterface
{
    /**
     * @param Model $model
     */
    public function getPk($model): string
    {
        return $model->getPk();
    }

    /**
     * @param Model $model
     */
    public function getTableName($model): string
    {
        return $model->getTable();
    }

    /**
     * @param Model $model
     */
    public function getDatabaseName($model): string
    {
        if (method_exists($model, 'getQuery')) {
            return $model->getQuery()->getConfig('database');
        }

        return $model->getConfig('database');
    }

    /**
     * @param Model $model
     *
     * @throws DbException
     */
    public function executeSQL($model, string $sql): mixed
    {
        if (method_exists($model, 'getQuery')) {
            return $model->getQuery()->getConnection()->query($sql);
        }

        /** @var PDOConnection $connection */
        $connection = $model->db()->getConnection();

        return $connection->query($sql);
    }

    /**
     * @param Model $model
     */
    public function getAttributes($model): array
    {
        return $model->toArray();
    }

    /**
     * @param Model $model
     */
    public function getChangedAttributes($model): array
    {
        return $model->getChangedData();
    }

    /**
     * @param Model $model
     */
    public function getValue($model, string $key): string
    {
        $keyText = $key . '_text';
        $value = $model->{$keyText} ?? $model->{$key};

        if ($value instanceof Raw) {
            return $value->getValue();
        }
        if (is_array($value) || is_object($value)) {
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
            $value = $model->getOrigin(vsprintf("json_extract(`json`, '$.name')", explode('->', $key, 2)));

            return trim($value, '"');
        }

        $keyText = $key . '_text';
        $attributeFun = 'get' . Str::studly(Str::lower($keyText)) . 'Attr';
        $value = method_exists($model, $attributeFun) ? $model->{$attributeFun}($model->getOrigin($key)) : $model->getOrigin($key);

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    /**
     * @param Model $model
     */
    public function created($model, array $data): void
    {
        $model->setAttrs($data);
        $this->generateLog($model, self::CREATED);
    }

    /**
     * @param Model $model
     */
    public function updated($model, array $oldData, array $data): void
    {
        $model->setAttrs($oldData);
        $model->refreshOrigin();
        $model->setAttrs($data);
        $this->generateLog($model, self::UPDATED);
    }

    /**
     * @param Model $model
     */
    public function deleted($model, array $data): void
    {
        $model->setAttrs($data);
        $this->generateLog($model, self::DELETED);
    }

    /**
     * @param Model $model
     */
    public function batchCreated($model, array $data): void
    {
        foreach ($data as $item) {
            $model->setAttrs($item);
            $this->generateLog($model, self::BATCH_CREATED);
        }
    }

    /**
     * @param Model $model
     */
    public function batchUpdated($model, array $oldData, array $data): void
    {
        foreach ($oldData as $item) {
            $model->setAttrs($item);
            $model->refreshOrigin();
            $model->setAttrs($data);
            $this->generateLog($model, self::BATCH_UPDATED);
        }
    }

    /**
     * @param Model $model
     */
    public function batchDeleted($model, array $data): void
    {
        foreach ($data as $item) {
            $model->setAttrs($item);
            $this->generateLog($model, self::BATCH_DELETED);
        }
    }
}
