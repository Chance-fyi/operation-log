<?php
/**
 * Created by PhpStorm
 * Date 2023/7/11 15:18
 */

namespace Chance\Log\orm\hyperf;

use Hyperf\Database\ConnectionInterface as Query;
use Hyperf\Database\Model\Model;

class DbModel extends Model
{
    // The primary key name of the log record
    public string $logKey = 'id';

    private Query $query;

    public function setQueryObj(Query $query): void
    {
        $this->query = $query;
    }

    public function getQueryObj(): Query
    {
        return $this->query;
    }
}