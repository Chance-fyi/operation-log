<?php
/**
 * Created by PhpStorm
 * Date 2022/9/27 9:39
 */

require "vendor/autoload.php";

use Chance\Log\facades\OperationLog;
use Chance\Log\Test\Base;
use Chance\Log\Test\model\IUser;
use Chance\Log\Test\model\TUser;

use think\facade\Db;

new Base("");

$data = [
    "name" => rand(0, 100000),
    "sex" => rand(0, 1),
    "json" => array_rand(range(0, 100), 5),
];
$allData = [
    [
        "name" => rand(0, 100000),
        "sex" => rand(0, 1),
        "json" => array_rand(range(0, 100), 5),
    ],
    [
        "name" => rand(0, 100000),
        "sex" => rand(0, 1),
        "json" => array_rand(range(0, 100), 5),
    ],
    [
        "name" => rand(0, 100000),
        "sex" => rand(0, 1),
        "json" => array_rand(range(0, 100), 5),
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


//-------------DB-------------------
// DB类 新增单条
//Db::name("user")->json(["json"])->save($data);

// DB类 新增单条
//Db::name("user")->json(["json"])->insert($data);

// DB类 新增单条 返回id
//Db::name("user")->json(["json"])->insertGetId($data);

// DB类 新增多条
//Db::name("user")->json(["json"])->insertAll($allData);

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


//-------------DB-------------------
// 包含主键更新单条
//Db::name("user")->json(["json"])->save(["id" => 1] + $data);

// 带条件更新多条
//Db::name("user")->json(["json"])->where("id","<", 10)->save($data);

// 带条件更新单条
//Db::name("user")->json(["json"])->where("id","=", 10)->save($data);

// 带条件更新多条
//Db::name("user")->json(["json"])->where("id","<", 10)->update($data);

// 带条件更新单条
//Db::name("user")->json(["json"])->where("id","=", 10)->update($data);

// 包含主键更新单条
//Db::name("user")->json(["json"])->update(["id" => 1] + $data);

// 自增 单条
//Db::name("user")->json(["json"])->where("id", 1)->inc("name", 5)->update();

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


//-------------DB-------------------
// 单条删除
//Db::name("user")->delete(1);
// 根据id多条删除
//Db::name("user")->delete([1,2,3]);
// 条件单条删除
//Db::name("user")->where("id", 1)->delete();
// 条件多条删除
//Db::name("user")->where("id","<",10)->delete();

Db::rollback();

//-----------------------------------------------------多数据库---------------------------------------------
//$user->insert($data);
//Db::name("user")->insert($data);
//Db::connect("default1")->name("user")->insert($data);

//=====================================================IlluminateORM======================================
use Illuminate\Database\Capsule\Manager;

$user = new IUser();
//-----------------------------------------------------新增------------------------------------------------
//-------------Model----------------
// 单条新增
//$user->name = rand(0, 100000);
//$user->sex = rand(0, 1);
//$user->save();

// 单条新增
//IUser::create($data);


//-------------DB-------------------
// insert 单条、多条新增
//Manager::table("user")->insert($data);
//Manager::table("user")->insert($allData);

// insertGetId 单条新增
//Manager::table("user")->insertGetId($data);

// insertOrIgnore 单条、多条新增
//Manager::table("user")->insertOrIgnore($data);
//Manager::table("user")->insertOrIgnore($allData);

//-----------------------------------------------------修改------------------------------------------------
//-------------Model----------------
//$user = IUser::find(1);
//$user->name = rand(0, 100000);
//$user->sex = rand(0, 1);
//$user->save();

// 批量修改
//IUser::query()->where("id", "<", 10)->update($data);

//-------------DB-------------------
// 单条修改
//Manager::table("user")->where("id", "=", 1)->update($data);

// 多条修改
//Manager::table("user")->where("id", "<", 10)->update($data);

// 自增 自减 单条、多条
//Manager::table("user")->where("id", "=", 1)->increment("name", 5);
//Manager::table("user")->where("id", "<", 10)->increment("name", 5);
//Manager::table("user")->where("id", "=", 1)->increment("name", 5);
//Manager::table("user")->where("id", "<", 10)->decrement("name", 5);
// 指定要更新的其他列
//Manager::table("user")->where("id", "<", 10)->decrement("name", 5, ["sex" => 1]);

//-----------------------------------------------------删除------------------------------------------------
Manager::beginTransaction();
//echo Manager::table("user")->count() . PHP_EOL;
//-------------Model----------------
// 单条删除
//$user = IUser::find(1);
//$user->delete();

//IUser::destroy(1);
//IUser::destroy(1, 2, 3);
//IUser::destroy([1, 2, 3]);
//IUser::destroy(collect([1, 2, 3]));

//-------------DB-------------------
// 不带条件 删除全部
//Manager::table("user")->delete();

// 删除一条
//Manager::table("user")->delete(1);
//Manager::table("user")->delete(9999999);

// 删除多条
//Manager::table("user")->where("id", "<", 10)->delete();


//echo Manager::table("user")->count() . PHP_EOL;
Manager::rollBack();

// 清空表
//Manager::table("user")->truncate();

//-----------------------------------------------------多数据库---------------------------------------------
//$user->insert($data);
//Manager::table("user")->insert($data);
//Manager::connection("default1")->table("user")->insert($data);

echo OperationLog::getLog() . PHP_EOL;