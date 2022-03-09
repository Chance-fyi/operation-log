<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:20
 */

namespace Chance\Log\Test\model;

use Chance\Log\RegisterThinkOrmEvent;
use think\Model;

class TBase extends Model
{
    use RegisterThinkOrmEvent;
}