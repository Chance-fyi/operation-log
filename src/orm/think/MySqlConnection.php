<?php
/**
 * Created by PhpStorm
 * Date 2022/12/21 17:24
 */

namespace Chance\Log\orm\think;

use Chance\Log\facades\ThinkOrmLog;
use think\db\connector\Mysql;

class MySqlConnection extends Mysql
{
    public function startTrans(): void
    {
        ThinkOrmLog::beginTransaction();
        parent::startTrans();
    }

    public function rollback(): void
    {
        ThinkOrmLog::rollBackTransaction();
        parent::rollback();
    }
}