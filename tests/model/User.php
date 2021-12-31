<?php
/**
 * Created by PhpStorm
 * User Chance
 * Date 2021/12/31 11:12
 */

namespace Chance\Log\Test\model;

class User extends Base
{
    protected $table = 'user';

    public function getSexTextAttribute($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}