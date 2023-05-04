<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 13:25
 */

namespace Chance\Log\Test\illuminate\model;

class Comment extends Connection
{
    public $tableComment = '用户';
    public $columnComment = [
        'name' => '姓名',
        'phone' => '手机号',
        'email' => '邮箱',
        'sex' => '性别',
        'age' => '年龄',
    ];
}