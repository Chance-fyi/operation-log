<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:18
 */

namespace Chance\Log;

use think\facade\Db;
use think\helper\Str;
use think\Model;

class ThinkOrmLog extends OperationLog implements OperationLogInterface
{

    public static function created($model)
    {
        self::$log .= self::createOrDelete($model, '添加');
    }

    public static function updated($model)
    {
        $logKey = $model->logKey;
        $message = '修改了 ' . self::getTableComment($model)
            . '(' . self::getColumnComment($model, $logKey) . ":{$model->$logKey})：";

        foreach ($model->getChangedData() as $key => $value) {
            $keyText = $key . '_text';
            $attributeFun = 'get' . Str::studly(Str::lower($keyText)) . 'Attr';
            $message .= self::getColumnComment($model, $key)
                . "由：" . (method_exists($model, $attributeFun) ? $model->$attributeFun($model->getOrigin($key)) : $model->getOrigin($key)) . ' '
                . "改为：" . ($model->$keyText ?? $value) . ' ';
        }

        self::$log .= $message . PHP_EOL;
    }

    public static function deleted($model)
    {
        self::$log .= self::createOrDelete($model, '删除');
    }

    protected static function createOrDelete(Model $model, $message): string
    {
        $logKey = $model->logKey;
        $message = $message . '了 ' . self::getTableComment($model)
            . '(' . self::getColumnComment($model, $logKey) . ":{$model->$logKey})：";

        foreach ($model->toArray() as $key => $value) {
            if ($logKey === $key) {
                continue;
            }
            $keyText = $key . '_text';
            $message .= self::getColumnComment($model, $key)
                . "：" . ($model->$keyText ?? $value) . ' ';
        }
        return $message . PHP_EOL;
    }

    /**
     * Notes: 获取表注释
     * DateTime: 2021/12/31 13:13
     * @param Model $model
     * @return string
     */
    public static function getTableComment($model): string
    {
        $table = $model->getTable();
        if (isset($model->tableComment)){
            return $model->tableComment ?: $table;
        }

        $dbName = $model->getConfig('database');
        $comment = "";

        if (empty(self::$tableComment[$dbName])) {
            self::$tableComment[$dbName] = Db::query("SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$dbName}'");
        }

        foreach (self::$tableComment[$dbName] as $item) {
            if ($item['TABLE_NAME'] == $table) {
                $comment = $item['TABLE_COMMENT'];
            }
        }
        return $comment ?: $table;
    }

    /**
     * Notes: 获取字段注释
     * DateTime: 2021/12/31 13:13
     * @param Model $model
     * @param $field
     * @return string
     */
    public static function getColumnComment($model, $field): string
    {
        if (isset($model->columnComment)){
            return $model->columnComment[$field] ?? $field;
        }

        $dbName = $model->getConfig('database');
        $table = $model->getTable();
        $comment = "";

        if (empty(self::$columnComment[$dbName])) {
            self::$columnComment[$dbName] = Db::query("SELECT TABLE_NAME,COLUMN_NAME,COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$dbName}'");
        }
        foreach (self::$columnComment[$dbName] as $item) {
            if ($item['TABLE_NAME'] == $table && $item['COLUMN_NAME'] == $field) {
                $comment = $item['COLUMN_COMMENT'];
            }
        }
        return $comment ?: $field;
    }
}