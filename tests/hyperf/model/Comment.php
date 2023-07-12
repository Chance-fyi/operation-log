<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 13:25.
 */

namespace Chance\Log\Test\hyperf\model;

class Comment extends Connection
{
    public string $tableComment = '用户';
    public array $columnComment = [
        'name' => '姓名',
        'phone' => '手机号',
        'email' => '邮箱',
        'sex' => '性别',
        'age' => '年龄',
    ];
}
