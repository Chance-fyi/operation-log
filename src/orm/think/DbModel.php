<?php
/**
 * Created by PhpStorm
 * Date 2022/10/7 11:44.
 */

namespace Chance\Log\orm\think;

use think\db\BaseQuery as Query;
use think\Model;

class DbModel extends Model
{
    // The primary key name of the log record
    public string $logKey = 'id';

    protected $autoWriteTimestamp = true;

    private Query $query;

    public function __construct(string $table, array $data = [])
    {
        $this->table = $table;
        parent::__construct($data);
    }

    public function setQuery(Query $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }
}
