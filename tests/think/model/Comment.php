<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 17:03.
 */

namespace Chance\Log\Test\think\model;

class Comment extends User
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
