<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 10:27
 */

namespace Chance\Log;

/**
 * @method getTableName($model)
 * @method getDatabaseName($model)
 * @method executeSQL($sql)
 * @method getAttributes($model)
 * @method getChangedAttributes($model)
 * @method getValue($model, string $key)
 * @method getOldValue($model, string $key)
 */
class OperationLog
{
    // 表注释
    protected $tableComment;

    // 字段注释
    protected $columnComment;

    // 日志
    protected $log = '';

    const CREATED = 'created';
    const UPDATED = 'updated';
    const DELETED = 'deleted';

    public function __construct()
    {
        Facade::setResolvedInstance(self::class, $this);
    }

    public function getLog(): string
    {
        $log = $this->log;
        $this->log = '';
        return trim($log, PHP_EOL);
    }

    public function clear()
    {
        $this->tableComment = [];
        $this->columnComment = [];
        $this->log = '';
    }

    /**
     * Notes: 获取表注释
     * DateTime: 2021/12/31 13:13
     * @param $model
     * @return string
     */
    public function getTableComment($model): string
    {
        $table = $this->getTableName($model);
        if (isset($model->tableComment)) {
            return $model->tableComment ?: $table;
        }

        $databaseName = $this->getDatabaseName($model);
        $comment = "";

        if (empty($this->tableComment[$databaseName])) {
            $this->tableComment[$databaseName] = $this->executeSQL("SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '$databaseName'");
        }

        foreach ($this->tableComment[$databaseName] as $item) {
            if ($item['TABLE_NAME'] == $table) {
                $comment = $item['TABLE_COMMENT'];
                break;
            }
        }
        return $comment ?: $table;
    }

    /**
     * Notes: 获取字段注释
     * DateTime: 2021/12/31 13:13
     * @param $model
     * @param $field
     * @return string
     */
    public function getColumnComment($model, $field): string
    {
        if (isset($model->columnComment)) {
            return $model->columnComment[$field] ?? $field;
        }

        $databaseName = $this->getDatabaseName($model);
        $table = $this->getTableName($model);
        $comment = "";

        if (empty($this->columnComment[$databaseName])) {
            $this->columnComment[$databaseName] = $this->executeSQL("SELECT TABLE_NAME,COLUMN_NAME,COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$databaseName'");
        }
        foreach ($this->columnComment[$databaseName] as $item) {
            if ($item->TABLE_NAME == $table && $item->COLUMN_NAME == $field) {
                $comment = $item->COLUMN_COMMENT;
                break;
            }
        }
        return $comment ?: $field;
    }

    public function generateLog($model, string $type)
    {
        $logKey = $model->logKey;
        $typeText = [
            self::CREATED => '创建',
            self::UPDATED => '修改',
            self::DELETED => '删除',
        ][$type];
        $log = "$typeText {$this->getTableComment($model)} ({$this->getColumnComment($model, $logKey)}:{$model->$logKey})：";

        switch ($type) {
            case self::CREATED:
            case self::DELETED:
                foreach ($this->getAttributes($model) as $key => $value) {
                    if ($logKey === $key) {
                        continue;
                    }
                    $log .= "{$this->getColumnComment($model, $key)}：{$this->getValue($model, $key)}，";
                }
                break;
            case self::UPDATED:
                foreach ($this->getChangedAttributes($model) as $key => $value) {
                    if ($logKey === $key) {
                        continue;
                    }
                    $log .= "{$this->getColumnComment($model, $key)}由：{$this->getOldValue($model, $key)} 改为：{$this->getValue($model, $key)}，";
                }
                break;
        }
        $this->log .= trim($log, '，') . PHP_EOL;
    }
}