<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:20
 */

namespace Chance\Log\traits;

use Chance\Log\facades\ThinkOrmLog;

trait RegisterThinkOrmEvent
{
    // 日志记录的主键名称
    public $logKey = 'id';
    // 表注释
    public $tableComment = "";
    // 字段注释
    public $columnComment = [];

    public static function onAfterInsert($model)
    {
        ThinkOrmLog::created($model);
    }

    public static function onAfterUpdate($model)
    {
        $trace = debug_backtrace();
        $function = array_pop($trace)['function'];

        // 批量操作不执行事件
        if (in_array($function, ['update', 'saveAll'])) {
            return;
        }

        ThinkOrmLog::updated($model);
    }

    public static function onAfterDelete($model)
    {
        ThinkOrmLog::deleted($model);
    }

    public function save(array $data = [], string $sequence = null): bool
    {
        // saveAll 批量更新数据处理
        $pk = $this->getPk();
        if ($this->isExists() && isset($data[$pk])) {
            $this->setAttrs($this->find($data[$pk])->toArray());
            $this->refreshOrigin();
        }

        return parent::save($data, $sequence);
    }

    public function insert(array $data = [], bool $getLastInsID = false)
    {
        $result = parent::insert($data, $getLastInsID);

        $data[$this->logKey] = $this->getLastInsID();
        ThinkOrmLog::insert($this, $data);

        return $result;
    }

    public function insertAll(array $dataSet = [], int $limit = 0): int
    {
        $result = parent::insertAll($dataSet, $limit);

        ThinkOrmLog::insertAll($this, $dataSet);

        return $result;
    }

    public static function update(array $data, $where = [], array $allowField = [], string $suffix = '')
    {
        $model = new static();
        $pk = $model->getPk();
        if (isset($data[$pk])) {
            // 包含主键只更新一条
            $model->setAttrs($model->find($data[$pk])->toArray());
            $model->refreshOrigin();
            $model->setAttrs($data);
            ThinkOrmLog::updated($model);
        } elseif (!empty($where)) {
            // 传入条件批量更新
            $field = $allowField ?: array_keys($data);
            $field[] = $model->logKey;
            $oldData = $model->where($where)->field($field)->select()->toArray();
            ThinkOrmLog::update($model, $oldData, $data);
        }

        return parent::update($data, $where, $allowField, $suffix);
    }
}