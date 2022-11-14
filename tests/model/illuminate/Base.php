<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 11:10
 */

namespace Chance\Log\Test\model\illuminate;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    public $timestamps = false;
    protected $guarded = [];
}