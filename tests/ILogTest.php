<?php
/**
 * Created by PhpStorm
 * IUser Chance
 * Date 2021/12/31 16:50
 */

namespace Chance\Log\Test;

use Chance\Log\OperationLog;
use Chance\Log\Test\model\IUser;

class ILogTest extends Base
{
    public function testCreate()
    {
        $user = new IUser();
        $user->name = 'Create';
        $user->sex = 1;
        $user->save();
        $id = $user->id;
        $this->assertEquals("添加了 用户(id:{$id})：姓名：Create 性别：男 " . PHP_EOL, OperationLog::getMessage());
        return $id;
    }

    /**
     * @depends testCreate
     */
    public function testUpdate($id)
    {
        $user = IUser::find($id);
        $user->name = 'Update';
        $user->sex = 0;
        $user->save();
        $this->assertEquals("修改了 用户(id:{$id})：姓名由：Create 改为：Update 性别由：男 改为：女 " . PHP_EOL, OperationLog::getMessage());
        return $id;
    }

    /**
     * @depends testUpdate
     */
    public function testDelete($id)
    {
        IUser::destroy($id);
        $this->assertEquals("删除了 用户(id:{$id})：姓名：Update 性别：女 " . PHP_EOL, OperationLog::getMessage());
    }
}
