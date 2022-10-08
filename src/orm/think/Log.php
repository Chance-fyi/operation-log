<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:18
 */

namespace Chance\Log\orm\think;

use Chance\Log\OperationLog;
use Chance\Log\OperationLogInterface;
use think\db\Raw;
use think\facade\Db;
use think\helper\Str;
use think\Model;

class Log extends OperationLog implements OperationLogInterface
{
    /**
     * DateTime: 2022/10/8 10:56
     * @param Model $model
     * @return string
     */
    public function getPk($model): string
    {
        return $model->getPk();
    }

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
        return $model->getConfig("database");
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
        $keyText = $key . "_text";
        $value = $model->$keyText ?? $model->$key;

        if (is_array($value)) {
            return implode(" ", $value);
        } elseif ($value instanceof Raw) {
            return $value->getValue();
        } else {
            return $value;
        }
    }

    /**
     * @param Model $model
     * @param string $key
     * @return string
     */
    public function getOldValue($model, string $key): string
    {
        $keyText = $key . "_text";
        $attributeFun = "get" . Str::studly(Str::lower($keyText)) . "Attr";
        return (string)(method_exists($model, $attributeFun) ? $model->$attributeFun($model->getOrigin($key)) : $model->getOrigin($key));
    }

    /**
     * DateTime: 2022/10/7 18:10
     * @param $model
     * @param array $data
     */
    public function created($model, array $data)
    {
        $model->setAttrs($data);
        $this->generateLog($model, self::CREATED);
    }

    /**
     * DateTime: 2022/10/7 18:10
     * @param Model $model
     * @param array $oldData
     * @param array $data
     */
    public function updated($model, array $oldData, array $data)
    {
        $model->setAttrs($oldData);
        $model->refreshOrigin();
        $model->setAttrs($data);
        $this->generateLog($model, self::UPDATED);
    }

    /**
     * DateTime: 2022/10/7 18:10
     * @param Model $model
     * @param array $data
     */
    public function deleted($model, array $data)
    {
        $model->setAttrs($data);
        $this->generateLog($model, self::DELETED);
    }

    /**
     * DateTime: 2022/10/7 18:11
     * @param Model $model
     * @param array $data
     */
    public function batchCreated($model, array $data)
    {
        foreach ($data as $item) {
            $model->setAttrs($item);
            $this->generateLog($model, self::BATCH_CREATED);
        }
    }

    /**
     * DateTime: 2022/10/7 18:11
     * @param Model $model
     * @param array $oldData
     * @param array $data
     */
    public function batchUpdated($model, array $oldData, array $data)
    {
        foreach ($oldData as $item) {
            $model->setAttrs($item);
            $model->refreshOrigin();
            $model->setAttrs($data);
            $this->generateLog($model, self::BATCH_UPDATED);
        }
    }

    /**
     * DateTime: 2022/10/7 18:11
     * @param Model $model
     * @param array $data
     */
    public function batchDeleted($model, array $data)
    {
        foreach ($data as $item) {
            $model->setAttrs($item);
            $this->generateLog($model, self::BATCH_DELETED);
        }
    }
}