<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 14:06.
 */

namespace Chance\Log\Test\illuminate\model;

class DoNotRecordLog extends User
{
    public bool $doNotRecordLog = true;
}
