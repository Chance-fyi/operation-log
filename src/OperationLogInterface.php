<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 9:48
 */

namespace Chance\Log;

interface OperationLogInterface
{
    /**
     * Notes: 获取表名
     * DateTime: 2022/9/28 17:22
     * @param $model
     * @return string
     */
    public function getTableName($model): string;

    /**
     * Notes: 获取数据库名
     * DateTime: 2022/9/28 17:25
     * @param $model
     * @return string
     */
    public function getDatabaseName($model): string;

    /**
     * Notes: 执行SQL
     * DateTime: 2022/9/28 17:29
     * @param string $sql
     * @return mixed
     */
    public function executeSQL(string $sql);

    /**
     * Notes: 获取模型上当前所有的属性
     * DateTime: 2022/10/6 14:33
     * @param $model
     * @return array
     */
    public function getAttributes($model): array;

    /**
     * Notes: 获取模型上当前修改的属性
     * DateTime: 2022/10/6 14:34
     * @param $model
     * @return array
     */
    public function getChangedAttributes($model): array;

    public function getValue($model, string $key): string;

    public function getOldValue($model, string $key): string;

    public function created($model);

    public function updated($model);

    public function deleted($model);

    public function batchCreated($model, array $data);

    public function batchUpdated($model, array $oldData, array $data);

    public function batchDeleted($model, array $data);

}