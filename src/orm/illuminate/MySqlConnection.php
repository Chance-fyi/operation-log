<?php
/**
 * Created by PhpStorm
 * Date 2022/10/8 9:54
 */

namespace Chance\Log\orm\illuminate;

use Chance\Log\facades\IlluminateOrmLog;

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
        IlluminateOrmLog::beginTransaction();
        parent::beginTransaction();
    }

    public function rollBack($toLevel = null)
    {
        IlluminateOrmLog::rollBackTransaction();
        parent::rollBack($toLevel);
    }
}