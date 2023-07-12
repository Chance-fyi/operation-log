<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 13:58.
 */

namespace Chance\Log\Test\hyperf\model;

class IgnoreLogFields extends User
{
    public array $ignoreLogFields = [
        'create_time',
        'update_time',
    ];
}
