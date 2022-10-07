<?php
/**
 * Created by PhpStorm
 * Date 2022/9/27 9:39
 */

require "vendor/autoload.php";

use Chance\Log\facades\OperationLog;
use Chance\Log\Test\Base;
use Chance\Log\Test\model\TUser;
use think\facade\Db;

new Base("");

$data = [
    "name" => rand(0, 100000),
    "sex" => rand(0, 1),
];
$allData = [
    [
        "name" => rand(0, 100000),
        "sex" => rand(0, 1),
    ],
    [
        "name" => rand(0, 100000),
        "sex" => rand(0, 1),
    ],
    [
        "name" => rand(0, 100000),
        "sex" => rand(0, 1),
    ]
];

//=====================================================ThinkORM===========================================
$user = new TUser();
//-----------------------------------------------------新增------------------------------------------------
//-------------Model----------------

// 新增单条
//$user->save($data);

// 循环新增多条
//$user->saveAll($allData);

// 新增单条
//$user->insert($data);

// 新增单条
//TUser::create($data);

// 批量新增 无法获取id
//$user->insertAll($allData);

//-------------DB----------------
// DB类 新增单条
//Db::name("user")->save($data);

// DB类 新增单条
//Db::name("user")->insert($data);

// DB类 新增单条 返回id
//Db::name("user")->insertGetId($data);

// DB类 新增多条
//Db::name("user")->insertAll($allData);

//-----------------------------------------------------修改------------------------------------------------
//-------------Model----------------

// 根据条件批量修改
//TUser::update($data, ["sex" => 1]);

// 包含主键单条修改
//TUser::update(["id" => 1] + $data);

// 包含主键单条修改
//TUser::update(["id" => 1] + $data, ["sex" => 1]);

// 包含主键 循环多条修改
//$user->saveAll([
//    ["id" => 1] + $data,
//    ["id" => 2] + $data
//]);

// 根据条件批量修改
//TUser::where("id","<",10)->save($data);

// 自增
//TUser::where("id", 1)->inc("name", 1)->update();

//-------------DB----------------
// 包含主键更新单条
//Db::name("user")->save(["id" => 1] + $data);

// 带条件更新多条
//Db::name("user")->where("id","<", 10)->save($data);

// 带条件更新单条
//Db::name("user")->where("id","=", 10)->save($data);

// 带条件更新多条
//Db::name("user")->where("id","<", 10)->update($data);

// 带条件更新单条
//Db::name("user")->where("id","=", 10)->update($data);

// 包含主键更新单条
//Db::name("user")->update(["id" => 1] + $data);

// 自增 单条
//Db::name("user")->where("id", 1)->inc("name", 5)->update();

// 自减 多条
//Db::name("user")->where("id", "<", 10)->dec("name", 5)->update();

// raw方法
//Db::name("user")->where("id", "<", 10)->update(["name" => Db::raw("name - 1")]);
//-----------------------------------------------------删除------------------------------------------------

Db::startTrans();
//-------------Model----------------
// 单条删除
//$user = TUser::find(1);
//$user->delete();

// 单条删除
//TUser::destroy(1);

// 循环多条删除
//TUser::destroy([1,2,3]);

// 根据条件循环多条删除
//TUser::destroy(function($query){
//    $query->where("id",">",10);
//});
// 根据条件多条删除
//TUser::where("id", "<", 10)->delete();

//-------------DB----------------
// 单条删除
//Db::name("user")->delete(1);
// 根据id多条删除
//Db::name("user")->delete([1,2,3]);
// 条件单条删除
//Db::name("user")->where("id", 1)->delete();
// 条件多条删除
//Db::name("user")->where("id","<",10)->delete();

Db::rollback();

echo OperationLog::getLog() . PHP_EOL;