<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:18
 */

namespace Chance\Log\orm;

use Chance\Log\OperationLog;
use Chance\Log\OperationLogInterface;
use think\facade\Db;
use think\helper\Str;
use think\Model;

class ThinkOrmLog extends OperationLog implements OperationLogInterface
{
    /**
     * @param Model $model
     * @return string
     */
    public function getTableName($model): string
    {
        return $model->getTable();
    }

    /**
     * @param Model $model
     * @return string
     */
    public function getDatabaseName($model): string
    {
        return $model->getConfig('database');
    }

    public function executeSQL(string $sql)
    {
        return Db::query($sql);
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getAttributes($model): array
    {
        return $model->toArray();
    }

    /**
     * @param Model $model
     * @return array
     */
    public function getChangedAttributes($model): array
    {
        return $model->getChangedData();
    }

    /**
     * @param Model $model
     * @param string $key
     * @return string
     */
    public function getValue($model, string $key): string
    {
        $keyText = $key . '_text';
        return $model->$keyText ?? $model->$key;
    }

    /**
     * @param Model $model
     * @param string $key
     * @return string
     */
    public function getOldValue($model, string $key): string
    {
        $keyText = $key . '_text';
        $attributeFun = 'get' . Str::studly(Str::lower($keyText)) . 'Attr';
        return method_exists($model, $attributeFun) ? $model->$attributeFun($model->getOrigin($key)) : $model->getOrigin($key);
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
}