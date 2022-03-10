<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 11:08
 */

namespace Chance\Log;

trait RegisterIlluminateOrmEvent
{
    // 日志记录的主键名称
    public $logKey = 'id';

    protected static function booted()
    {
        static::created(function ($model){
            IlluminateOrmLog::created($model);
        });

        static::updated(function ($model){
            IlluminateOrmLog::updated($model);
        });

        static::deleted(function ($model){
            IlluminateOrmLog::deleted($model);
        });
    }
}