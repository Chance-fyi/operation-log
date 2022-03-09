<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 9:48
 */

namespace Chance\Log;

interface OperationLogInterface
{
    public static function created($model);

    public static function updated($model);

    public static function deleted($model);

    public static function getTableComments($model);

    public static function getColumnComment($model, $field);

    public static function getMessage();
}