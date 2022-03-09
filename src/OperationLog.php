<?php
/**
 * Created by PhpStorm
 * Date 2022/3/9 10:27
 */

namespace Chance\Log;

class OperationLog
{
    // 表注释
    protected static array $tableComment;
    // 字段注释
    protected static array $columnComment;

    // 日志
    protected static string $log = '';

    public static function getLog(): string
    {
        $message = self::$log;
        self::$log = '';
        return $message;
    }

    public static function clear()
    {
        self::$tableComment = [];
        self::$columnComment = [];
        self::$log = '';
    }
}