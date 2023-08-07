<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 10:27.
 */

namespace Chance\Log;

use Chance\Log\facades\OperationLog as OperationLogFacade;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use think\Model as ThinkModel;
use Webman\Context as WebmanContext;

/**
 * @method getPk($model)
 * @method getTableName($model)
 * @method getDatabaseName($model)
 * @method executeSQL($model, $sql)
 * @method getAttributes($model)
 * @method getChangedAttributes($model)
 * @method getValue($model, string $key)
 * @method getOldValue($model, string $key)
 */
class OperationLog
{
    public const CREATED = 'created';
    public const BATCH_CREATED = 'batch_created';
    public const UPDATED = 'updated';
    public const BATCH_UPDATED = 'batch_updated';
    public const DELETED = 'deleted';
    public const BATCH_DELETED = 'batch_deleted';
    private const CONTEXT_LOG = 'context_operation_log';

    protected array $tableComment;

    protected array $columnComment;

    protected array $log = [''];

    protected array $tableModelMapping = [];

    protected bool $status = true;

    public function __construct()
    {
        if (Facade::getResolvedInstance(self::class)) {
            $this->setTableModelMapping(OperationLogFacade::getTableModelMapping());
        }
        Facade::setResolvedInstance(self::class, $this);
    }

    public function getLog(): string
    {
        $log = $this->getRawLog();
        $this->clearLog();

        return trim(implode('', $log), PHP_EOL);
    }

    public function clearLog(): void
    {
        $this->setRawLog(['']);
    }

    public function beginTransaction(): void
    {
        $log = $this->getRawLog();
        $log[] = '';
        $this->setRawLog($log);
    }

    public function rollBackTransaction(int $toLevel): void
    {
        $this->setRawLog(array_slice($this->getRawLog(), 0, $toLevel));
        if (0 === count($this->getRawLog())) {
            $this->clearLog();
        }
    }

    /**
     * Get table comment.
     */
    public function getTableComment(ThinkModel|LaravelModel $model): string
    {
        $table = $this->getTableName($model);
        if (isset($model->tableComment)) {
            return $model->tableComment ?: $table;
        }

        $databaseName = $this->getDatabaseName($model);
        $comment = '';

        if (empty($this->tableComment[$databaseName])) {
            $this->tableComment[$databaseName] = $this->executeSQL($model, "SELECT TABLE_NAME, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$databaseName}'");
        }

        foreach ($this->tableComment[$databaseName] as $item) {
            if (is_array($item) && $item['TABLE_NAME'] == $table) {
                $comment = $item['TABLE_COMMENT'];

                break;
            }
            if (is_object($item) && $item->TABLE_NAME == $table) {
                $comment = $item->TABLE_COMMENT;

                break;
            }
        }

        return (string) ($comment ?: $table);
    }

    /**
     * Get field comment.
     */
    public function getColumnComment(ThinkModel|LaravelModel $model, string $field): string
    {
        if (isset($model->columnComment)) {
            return $model->columnComment[$field] ?? $field;
        }

        $databaseName = $this->getDatabaseName($model);
        $table = $this->getTableName($model);
        $comment = '';

        if (empty($this->columnComment[$databaseName])) {
            $this->columnComment[$databaseName] = $this->executeSQL($model, "SELECT TABLE_NAME,COLUMN_NAME,COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$databaseName}'");
        }
        foreach ($this->columnComment[$databaseName] as $item) {
            if (is_array($item) && $item['TABLE_NAME'] == $table && $item['COLUMN_NAME'] == $field) {
                $comment = $item['COLUMN_COMMENT'];

                break;
            }
            if (is_object($item) && $item->TABLE_NAME == $table && $item->COLUMN_NAME == $field) {
                $comment = $item->COLUMN_COMMENT;

                break;
            }
        }

        return (string) ($comment ?: $field);
    }

    public function generateLog(ThinkModel|LaravelModel $model, string $type): void
    {
        if ($model->doNotRecordLog ?? false) {
            return;
        }
        $logKey = $model->logKey ?? $this->getPk($model);
        $typeText = [
            self::CREATED => '创建',
            self::BATCH_CREATED => '批量创建',
            self::UPDATED => '修改',
            self::BATCH_UPDATED => '批量修改',
            self::DELETED => '删除',
            self::BATCH_DELETED => '批量删除',
        ][$type];
        $logHeader = "{$typeText} {$this->getTableComment($model)}" .
            (in_array($type, [self::CREATED, self::UPDATED, self::BATCH_UPDATED, self::DELETED, self::BATCH_DELETED]) ? " ({$this->getColumnComment($model, $logKey)}:{$model->{$logKey}})：" : '：');
        $log = '';

        switch ($type) {
            case self::CREATED:
            case self::BATCH_CREATED:
            case self::DELETED:
            case self::BATCH_DELETED:
                foreach ($this->getAttributes($model) as $key => $value) {
                    if ($logKey === $key
                        || (isset($model->ignoreLogFields) && is_array($model->ignoreLogFields) && in_array($key, $model->ignoreLogFields))) {
                        continue;
                    }
                    $log .= "{$this->getColumnComment($model, $key)}：{$this->getValue($model, $key)}，";
                }

                break;

            case self::UPDATED:
            case self::BATCH_UPDATED:
                foreach ($this->getChangedAttributes($model) as $key => $value) {
                    $keys = explode('.', $key);
                    $key = end($keys);
                    if ($logKey === $key
                        || (isset($model->ignoreLogFields) && is_array($model->ignoreLogFields) && in_array($key, $model->ignoreLogFields))) {
                        continue;
                    }
                    $log .= "{$this->getColumnComment($model, $key)}由：{$this->getOldValue($model, $key)} 改为：{$this->getValue($model, $key)}，";
                }

                break;
        }
        if (!empty($log)) {
            $log = mb_substr($log, 0, mb_strlen($log, 'utf8') - 1, 'utf8');
            $logs = $this->getRawLog();
            array_splice($logs, -1, 1, end($logs) . $logHeader . $log . PHP_EOL);
            $this->setRawLog($logs);
        }
    }

    public function setTableModelMapping(array $map): void
    {
        $this->tableModelMapping = $map;
    }

    public function getTableModelMapping(): array
    {
        return $this->tableModelMapping;
    }

    public function status(): bool
    {
        return $this->status;
    }

    public function enable(): void
    {
        $this->status = true;
    }

    public function disable(): void
    {
        $this->status = false;
    }

    private function getRawLog()
    {
        if (class_exists(WebmanContext::class)) {
            return WebmanContext::get(self::CONTEXT_LOG) ?? [''];
        }

        return $this->log;
    }

    private function setRawLog(array $log): void
    {
        if (class_exists(WebmanContext::class)) {
            WebmanContext::set(self::CONTEXT_LOG, $log);

            return;
        }
        $this->log = $log;
    }
}
