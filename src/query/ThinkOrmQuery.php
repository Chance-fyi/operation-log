<?php
/**
 * Created by PhpStorm
 * Date 2022/10/7 16:19
 */

namespace Chance\Log\query;

use Chance\Log\DbModel;
use Chance\Log\facades\ThinkOrmLog;
use think\db\Query;
use think\Model;

class ThinkOrmQuery extends Query
{
    public function insert(array $data = [], bool $getLastInsID = false)
    {
        $result = parent::insert($data, $getLastInsID);

        if ($getLastInsID) {
            $id = $result;
        } else {
            $id = $this->getLastInsID();
        }

        $model = $this->generateModel($this);
        $pk = $this->getPk();
        $data = $data ?: $this->getOptions("data");
        $data[$pk] = $id;
        $model->setAttrs($data);
        ThinkOrmLog::created($model);

        return $result;
    }

    public function insertAll(array $dataSet = [], int $limit = 0): int
    {
        $result = parent::insertAll($dataSet, $limit);

        $model = $this->generateModel($this);
        ThinkOrmLog::batchCreated($model, $dataSet);

        return $result;
    }

    public function update(array $data = []): int
    {
        $model = $this->generateModel($this);
        $newData = $data ?: $this->getOptions("data");
        $field = array_keys($newData);
        $field[] = $model->logKey ?? $model->getPk();

        $pk = $model->getPk();
        if (isset($data[$pk])) {
            // 包含主键只更新一条
            $oldData = $this->find($data[$pk]);
            if (!empty($oldData)) {
                $oldData = [is_array($oldData) ? $oldData : $oldData->toArray()];
            }
        } else {
            // 条件查询或许是多条
            $oldData = $this->field($field)->select()->toArray();
        }
        if (!empty($oldData)) {
            if (count($oldData) > 1) {
                ThinkOrmLog::batchUpdated($model, $oldData, $newData);
            } else {
                $model->setAttrs($oldData[0]);
                $model->refreshOrigin();
                $model->setAttrs($newData);
                ThinkOrmLog::updated($model);
            }
        }

        return parent::update($data);
    }

    public function delete($data = null): int
    {
        $model = $this->generateModel($this);
        if (!empty($data)) {
            $pk = $model->getPk();
            $delData = $this->whereIn($pk, $data)->select()->toArray();
        } else {
            $delData = $this->select()->toArray();
        }

        if (!empty($delData)) {
            if (count($delData) > 1) {
                ThinkOrmLog::batchDeleted($model, $delData);
            } else {
                $model->setAttrs($delData[0]);
                ThinkOrmLog::deleted($model);
            }
        }


        return parent::delete($data);
    }

    /**
     * Notes: 生成Model对象
     * DateTime: 2022/10/7 16:34
     * @param Query $query
     * @return Model
     */
    private function generateModel(Query $query): Model
    {
        if ($query->getModel()) {
            return $query->getModel();
        }

        $name = $query->getName();
        $modelNamespace = $query->getConfig("modelNamespace") ?: "app\model";
        $className = trim($modelNamespace, "\\") . "\\" . ucfirst($name);
        if (class_exists($className)) {
            $model = new $className;
        } else {
            $model = new DbModel($name);
            $model->table($query->getTable());
            $model->logKey = $query->getConfig("logKey") ?: $model->getPk();
            $model->pk($query->getPk());
        }
        return $model;
    }
}