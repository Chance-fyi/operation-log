<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 17:20.
 */

namespace Chance\Log\Test\think\model;

class IgnoreLogFields extends User
{
    public array $ignoreLogFields = [
        'create_time',
        'update_time',
    ];
}
