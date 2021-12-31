<?php
/**
 * Created by PhpStorm
 * User Chance
 * Date 2021/12/31 11:08
 */

namespace Chance\Log;

trait RegistrationEvent
{
    // 日志记录的主键名称
    public string $logKey = 'id';

    protected static function booted()
    {
        static::created(function ($model){
            OperationLog::created($model);
        });

        static::updated(function ($model){
            OperationLog::updated($model);
        });

        static::deleted(function ($model){
            OperationLog::deleted($model);
        });
    }
}