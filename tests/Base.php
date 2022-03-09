<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 16:40
 */

namespace Chance\Log\Test;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Events\Dispatcher;
use PHPUnit\Framework\TestCase;

class Base extends TestCase
{
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->connection();
        $this->createTable();
    }

    // 连接数据库
    protected function connection()
    {
        $capsule = new Manager();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => 'mysql',
            'database' => 'test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'tb_',
        ]);
        // Set the event dispatcher used by Eloquent models... (optional)
        $capsule->setEventDispatcher(new Dispatcher(new Container));
        // Make this Capsule instance available globally via static methods... (optional)
        $capsule->setAsGlobal();
        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $capsule->bootEloquent();
    }

    // 创建表
    protected function createTable(){
        if (!Manager::select("show tables like 'tb_user';")){
            Manager::select("
                create table tb_user
                (
                    id   int auto_increment
                        primary key,
                    name varchar(20)       null comment '姓名',
                    sex  tinyint default 0 null comment '性别'
                )
                    comment '用户';
            ");
        }
    }
}