<?php
/**
 * Created by PhpStorm
 * User Chance
 * Date 2021/12/31 11:10
 */

namespace Chance\Log\Test\model;

use Chance\Log\RegistrationEvent;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    use RegistrationEvent;

    public $timestamps = false;
}