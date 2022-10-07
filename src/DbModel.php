<?php
/**
 * Created by PhpStorm
 * Date 2022/10/7 11:44
 */

namespace Chance\Log;

use think\Model;

class DbModel extends Model
{
    // 日志记录的主键名称
    public $logKey = "id";

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        parent::__construct($data);
    }
}