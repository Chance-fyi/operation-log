<?php
/**
 * Created by PhpStorm
 * Date 2022/10/8 10:24.
 */

namespace Chance\Log\orm\illuminate;

use Illuminate\Database\ConnectionInterface as Query;
use Illuminate\Database\Eloquent\Model;

class DbModel extends Model
{
    // The primary key name of the log record
    public string $logKey = 'id';

    private Query $query;

    public function setQuery(Query $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }
}
