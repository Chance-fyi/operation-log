<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 9:48.
 */

namespace Chance\Log;

use Hyperf\Database\Model\Model as HyperfModel;
use Illuminate\Database\Eloquent\Model as LaravelModel;
use think\Model as ThinkModel;

interface OperationLogInterface
{
    /**
     * Get primary key.
     */
    public function getPk(ThinkModel|LaravelModel|HyperfModel $model): string;

    /**
     * Get table name.
     */
    public function getTableName(ThinkModel|LaravelModel|HyperfModel $model): string;

    /**
     * Get database name.
     */
    public function getDatabaseName(ThinkModel|LaravelModel|HyperfModel $model): string;

    /**
     * Execute SQL.
     */
    public function executeSQL(ThinkModel|LaravelModel|HyperfModel $model, string $sql): mixed;

    /**
     * Obtain all current attributes on the model.
     */
    public function getAttributes(ThinkModel|LaravelModel|HyperfModel $model): array;

    /**
     * Obtain the currently modified properties on the model.
     */
    public function getChangedAttributes(ThinkModel|LaravelModel|HyperfModel $model): array;

    public function getValue(ThinkModel|LaravelModel|HyperfModel $model, string $key): string;

    public function getOldValue(ThinkModel|LaravelModel|HyperfModel $model, string $key): string;

    public function created(ThinkModel|LaravelModel|HyperfModel $model, array $data): void;

    public function updated(ThinkModel|LaravelModel|HyperfModel $model, array $oldData, array $data): void;

    public function deleted(ThinkModel|LaravelModel|HyperfModel $model, array $data): void;

    public function batchCreated(ThinkModel|LaravelModel|HyperfModel $model, array $data): void;

    public function batchUpdated(ThinkModel|LaravelModel|HyperfModel $model, array $oldData, array $data): void;

    public function batchDeleted($model, array $data): void;
}
