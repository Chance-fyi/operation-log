<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 11:20
 */

namespace Chance\Log;

trait RegisterThinkOrmEvent
{
    // 日志记录的主键名称
    public $logKey = 'id';

    public static function onAfterInsert($model){
        ThinkOrmLog::created($model);
    }

    public static function onAfterUpdate($model){
        ThinkOrmLog::updated($model);
    }

    public static function onAfterDelete($model){
        ThinkOrmLog::deleted($model);
    }
}