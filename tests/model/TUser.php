<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:21
 */

namespace Chance\Log\Test\model;

/**
 * @property mixed $id
 * @property mixed|string $name
 * @property int|mixed $sex
 */
class TUser extends TBase
{
    protected $name = "user";
    public $tableComment = "用户";
    public $columnComment = [
        "name" => "姓名",
        "sex" => "性别",
    ];

    public function getSexTextAttr($key): string
    {
        return ["女", "男"][($key ?? $this->sex)] ?? "未知";
    }
}