<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 13:38.
 */

namespace Chance\Log\Test\illuminate\model;

class Attribute extends Timestamps
{
    public function getSexTextAttribute($key): string
    {
        return ['女', '男'][$key ?? $this->sex] ?? '未知';
    }
}
