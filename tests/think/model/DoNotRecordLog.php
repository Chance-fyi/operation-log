<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 17:22.
 */

namespace Chance\Log\Test\think\model;

class DoNotRecordLog extends User
{
    public bool $doNotRecordLog = true;
}
