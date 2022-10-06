<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 11:10
 */

namespace Chance\Log\Test\model;

use Chance\Log\traits\RegisterIlluminateOrmEvent;
use Illuminate\Database\Eloquent\Model;

class IBase extends Model
{
    use RegisterIlluminateOrmEvent;

    public $timestamps = false;
}