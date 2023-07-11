<?php
/**
 * Created by PhpStorm
 * Date 2023/7/11 15:09
 */

namespace Chance\Log\orm\hyperf;

use Chance\Log\facades\HyperfOrmLog;

class MySqlConnection extends \Hyperf\Database\MySqlConnection
{
    public function query(): Builder
    {
        return new Builder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    public function beginTransaction(): void
    {
        HyperfOrmLog::beginTransaction();
        parent::beginTransaction();
    }

    public function rollBack($toLevel = null): void
    {
        HyperfOrmLog::rollBackTransaction(is_null($toLevel) ? $this->transactions : $toLevel);
        parent::rollBack($toLevel);
    }
}