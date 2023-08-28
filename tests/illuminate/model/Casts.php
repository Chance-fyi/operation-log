<?php
/**
 * Created by PhpStorm
 * Date 2023/8/28 11:27.
 */

namespace Chance\Log\Test\illuminate\model;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;

class Casts extends Timestamps
{
    protected $casts = [
        'json' => AsArrayObject::class,
    ];
}
