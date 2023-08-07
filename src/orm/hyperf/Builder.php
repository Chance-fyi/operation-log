<?php
/**
 * Created by PhpStorm
 * Date 2023/7/11 13:56.
 */

namespace Chance\Log\orm\hyperf;

use Chance\Log\facades\HyperfOrmLog;
use Chance\Log\facades\OperationLog;
use Hyperf\Database\Connection;
use Hyperf\Database\Model\Model;
use Hyperf\Stringable\Str;

class Builder extends \Hyperf\Database\Query\Builder
{
    public function insert(array $values): bool
    {
        $result = parent::insert($values);

        $this->insertLog($values);

        return $result;
    }

    public function insertGetId(array $values, $sequence = null): int
    {
        $id = parent::insertGetId($values, $sequence);

        $this->insertLog($values);

        return $id;
    }

    public function insertOrIgnore(array $values): int
    {
        $result = parent::insertOrIgnore($values);

        $this->insertLog($values);

        return $result;
    }

    public function update(array $values): int
    {
        if (HyperfOrmLog::status()) {
            $oldData = $this->get()->toArray();
            if (!empty($oldData)) {
                $model = $this->generateModel();
                if (count($oldData) > 1) {
                    HyperfOrmLog::batchUpdated($model, $oldData, $values);
                } else {
                    HyperfOrmLog::updated($model, (array) $oldData[0], $values);
                }
            }
        }

        return parent::update($values);
    }

    public function delete($id = null): int
    {
        $this->deleteLog($id);

        return parent::delete($id);
    }

    public function truncate(): void
    {
        $this->deleteLog();
        parent::truncate();
    }

    /**
     * Generate model object.
     */
    private function generateModel(): Model
    {
        $name = $this->from;

        /** @var Connection $connection */
        $connection = $this->getConnection();
        $database = $connection->getDatabaseName();
        $table = $connection->getTablePrefix() . $name;

        $mapping = [
            OperationLog::getTableModelMapping(),
            include __DIR__ . '/../../../cache/table-model-mapping.php',
        ];
        foreach ($mapping as $map) {
            if (is_array($map) && isset($map[$database][$table]) && class_exists($map[$database][$table])) {
                return new $map[$database][$table]();
            }
        }

        $modelNamespace = $connection->getConfig('modelNamespace') ?: 'app\\model';
        $className = trim($modelNamespace, '\\') . '\\' . Str::studly($name);
        if (class_exists($className)) {
            $model = new $className();
        } else {
            $model = new DbModel();
            $model->setQueryObj($connection);
            $model->setTable($name);
            $model->logKey = $connection->getConfig('logKey') ?: $model->getKeyName();
        }

        return $model;
    }

    private function insertLog(array $values): void
    {
        if (HyperfOrmLog::status()) {
            $model = $this->generateModel();
            if (is_array(reset($values))) {
                HyperfOrmLog::batchCreated($model, $values);
            } else {
                /** @var Connection $connection */
                $connection = $this->getConnection();
                $id = $connection->getPdo()->lastInsertId();
                $pk = $model->getKeyName();
                $values[$pk] = $id;
                HyperfOrmLog::created($model, $values);
            }
        }
    }

    private function deleteLog($id = null): void
    {
        if (HyperfOrmLog::status()) {
            if (!empty($id)) {
                $data = [(array) $this->find($id)];
            } else {
                $data = $this->get()->toArray();
            }

            if (!empty($data)) {
                $model = $this->generateModel();
                if (count($data) > 1) {
                    HyperfOrmLog::batchDeleted($model, $data);
                } else {
                    HyperfOrmLog::deleted($model, (array) $data[0]);
                }
            }
        }
    }
}
