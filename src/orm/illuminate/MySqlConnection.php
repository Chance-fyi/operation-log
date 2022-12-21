<?php
/**
 * Created by PhpStorm
 * Date 2022/10/8 9:54
 */

namespace Chance\Log\orm\illuminate;

use Chance\Log\facades\OperationLog;

class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
    public function query(): Builder
    {
        return new Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    public function beginTransaction()
    {
        OperationLog::beginTransaction();
        parent::beginTransaction();
    }

    public function rollBack($toLevel = null)
    {
        OperationLog::rollBackTransaction();
        parent::rollBack($toLevel);
    }
}