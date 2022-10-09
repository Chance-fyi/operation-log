<?php
/**
 * Created by PhpStorm
 * Date 2022/10/7 11:44
 */

namespace Chance\Log\orm\think;

use think\db\BaseQuery as Query;
use think\Model;

class DbModel extends Model
{
    // 日志记录的主键名称
    public $logKey = "id";

    /** @var Query $query */
    private $query;

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        parent::__construct($data);
    }

    public function setQuery(Query $query)
    {
        $this->query = $query;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }
}