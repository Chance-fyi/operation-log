<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 11:12
 */

namespace Chance\Log\Test\model;

class IUser extends IBase
{
    protected $table = 'user';
    public $tableComment = "用户";
    public $columnComment = [
        'name' => '姓名',
        'sex' => '性别',
    ];

    public function getSexTextAttribute($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}