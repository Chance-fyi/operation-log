<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 17:11
 */

namespace Chance\Log\Test\think\model;

class Attribute extends User
{
    public function getSexTextAttr($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}