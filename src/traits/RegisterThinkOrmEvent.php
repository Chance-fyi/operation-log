<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:20
 */

namespace Chance\Log\traits;

use Chance\Log\facades\ThinkOrmLog;

trait RegisterThinkOrmEvent
{
    // 日志记录的主键名称
    public $logKey = 'id';
    // 表注释
    public $tableComment = "";
    // 字段注释
    public $columnComment = [];

    public static function onAfterInsert($model)
    {
        ThinkOrmLog::created($model);
    }

    public static function onAfterUpdate($model)
    {
        ThinkOrmLog::updated($model);
    }

    public static function onAfterDelete($model)
    {
        ThinkOrmLog::deleted($model);
    }
}