<?php
/**
 * Created by PhpStorm
 * Date 2022/9/27 9:39
 */

require 'vendor/autoload.php';

use Chance\Log\facades\OperationLog;
use Chance\Log\Test\Base;
use Chance\Log\Test\model\TUser;
use think\facade\Db;

new Base('');

//=====================================================ThinkORM===========================================
$user = new TUser();
//-----------------------------------------------------新增------------------------------------------------

// 新增单条
//$user->save([
//    'name' => rand(0, 100000),
//    'sex' => rand(0, 1),
//]);

// 循环新增多条
//$user->saveAll([
//    [
//        'name' => rand(0, 100000),
//        'sex' => rand(0, 1),
//    ],
//    [
//        'name' => rand(0, 100000),
//        'sex' => rand(0, 1),
//    ]
//]);

// 新增单条
//$user->insert([
//    'name' => rand(0, 100000),
//    'sex' => rand(0, 1),
//]);

// 新增单条
//TUser::create([
//    'name' => rand(0, 100000),
//    'sex' => rand(0, 1),
//]);

// 批量新增 无法获取id
//$user->insertAll([
//    [
//        'name' => rand(0, 100000),
//        'sex' => rand(0, 1),
//    ],
//    [
//        'name' => rand(0, 100000),
//        'sex' => rand(0, 1),
//    ]
//]);

//-----------------------------------------------------修改------------------------------------------------

// 根据条件批量修改
//TUser::update([
//    'name' => rand(0, 100000),
//    'sex' => rand(0, 1),
//], ['sex' => 0]);

// 包含主键单条修改
//TUser::update([
//    'id' => 1,
//    'name' => rand(0, 100000),
//    'sex' => 0,
//]);

// 包含主键单条修改
//TUser::update([
//    'id' => 1,
//    'name' => rand(0, 100000),
//    'sex' => rand(0, 1),
//], ['sex' => 0]);

// 包含主键 循环多条修改
//$user->saveAll([
//    [
//        'id' => 1,
//        'name' => rand(0, 100000),
//        'sex' => rand(0, 1),
//    ],
//    [
//        'id' => 2,
//        'name' => rand(0, 100000),
//        'sex' => rand(0, 1),
//    ]
//]);

//-----------------------------------------------------删除------------------------------------------------

Db::startTrans();

// 单条删除
//$user = TUser::find(1);
//$user->delete();

// 单条删除
//TUser::destroy(1);

// 循环多条删除
//TUser::destroy([1,2,3]);

// 根据条件循环多条删除
//TUser::destroy(function($query){
//    $query->where('id','>',10);
//});

Db::rollback();

echo OperationLog::getLog() . PHP_EOL;