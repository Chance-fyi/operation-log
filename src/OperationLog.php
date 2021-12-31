<?php
/**
 * Created by PhpStorm
 * User Chance
 * Date 2021/12/31 11:10
 */

namespace Chance\Log;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OperationLog
{
    // 表注释
    protected static array $tableComment;
    // 字段注释
    protected static array $columnComment;

    // 日志
    protected static string $message = '';

    public static function created(Model $model)
    {
        self::$message .= self::createOrDelete($model, '添加');
    }

    public static function updated(Model $model)
    {
        $logKey = $model->logKey;
        $message = '修改了 ' . self::getTableComments($model)
            . '(' . (self::getColumnComment($model, $logKey) ?: $logKey) . ":{$model->$logKey})：";
        foreach ($model->getAttributes() as $key => $value) {
            if ($model->isClean($key)) {
                continue;
            }
            $keyText = $key . '_text';
            $attributeFun = 'get' . Str::studly(Str::lower($keyText)) . 'Attribute';
            $message .= (self::getColumnComment($model, $key) ?: $key)
                . "由：" . (method_exists($model, $attributeFun) ? $model->$attributeFun($model->getOriginal($key)) : $model->getOriginal($key)) . ' '
                . "改为：" . ($model->$keyText ?? $value) . ' ';
        }

        self::$message .= $message . PHP_EOL;
    }

    public static function deleted(Model $model)
    {
        self::$message .= self::createOrDelete($model, '删除');
    }

    protected static function createOrDelete(Model $model, $message): string
    {
        $logKey = $model->logKey;
        $message = $message . '了 ' . self::getTableComments($model)
            . '(' . (self::getColumnComment($model, $logKey) ?: $logKey) . ":{$model->$logKey})：";
        foreach ($model->getAttributes() as $key => $value) {
            if ($logKey === $key) {
                continue;
            }
            $keyText = $key . '_text';
            $message .= (self::getColumnComment($model, $key) ?: $key)
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
    protected static function getTableComments(Model $model): string
    {
        $dbName = $model->getConnection()->getDatabaseName();
        $table = $model->getConnection()->getTablePrefix() . $model->getTable();

        if (empty(self::$tableComment[$dbName])) {
            self::$tableComment[$dbName] = Manager::select("SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$dbName}'");
        }
        foreach (self::$tableComment[$dbName] as $item) {
            if ($item->TABLE_NAME == $table) {
                return $item->TABLE_COMMENT;
            }
        }
        return '';
    }

    /**
     * Notes: 获取字段注释
     * DateTime: 2021/12/31 13:13
     * @param Model $model
     * @param $field
     * @return string
     */
    protected static function getColumnComment(Model $model, $field): string
    {
        $dbName = $model->getConnection()->getDatabaseName();
        $table = $model->getConnection()->getTablePrefix() . $model->getTable();

        if (empty(self::$columnComment[$dbName])) {
            self::$columnComment[$dbName] = Manager::select("SELECT TABLE_NAME,COLUMN_NAME,COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$dbName}'");
        }
        foreach (self::$columnComment[$dbName] as $item) {
            if ($item->TABLE_NAME == $table && $item->COLUMN_NAME == $field) {
                return $item->COLUMN_COMMENT;
            }
        }
        return '';
    }

    public static function getMessage(): string
    {
        $message = self::$message;
        self::$message = '';
        return $message;
    }
}