<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:21
 */

namespace Chance\Log\Test\model;

class TUser extends TBase
{
    protected $name = 'user';

    public function getSexTextAttr($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}