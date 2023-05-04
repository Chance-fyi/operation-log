<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 10:42
 */

namespace Chance\Log\Test\think;

use Chance\Log\facades\OperationLog;
use Chance\Log\Test\think\model\Attribute;
use Chance\Log\Test\think\model\Comment;
use Chance\Log\Test\think\model\DoNotRecordLog;
use Chance\Log\Test\think\model\IgnoreLogFields;
use Chance\Log\Test\think\model\User;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

class ModelTest extends TestCase
{
    public function testCreated()
    {
        $data = mockData();
        $user = new User();
        $user->save($data);
        array_unshift($data, $user->id);
        $data['create_time'] = $user->create_time;
        $data['update_time'] = $user->update_time;
        $log = createLog($data);

        $data = mockData();
        $user = new User();
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->email = $data['email'];
        $user->sex = $data['sex'];
        $user->age = $data['age'];
        $user->save();
        array_unshift($data, $user->id);
        $data['create_time'] = $user->create_time;
        $data['update_time'] = $user->update_time;
        $log .= createLog($data);

        $data = mockData();
        $user = new User();
        $id = $user->insert($data, true);
        array_unshift($data, $id);
        $log .= createLog($data);

        $data = mockData();
        $user = User::create($data);
        array_unshift($data, $user->id);
        $data['create_time'] = $user->create_time;
        $data['update_time'] = $user->update_time;
        $log .= createLog($data);

        assertEquals(OperationLog::getLog(), trim($log));
        assertEmpty(OperationLog::getLog());

        User::create(mockData());
        OperationLog::clearLog();
        assertEmpty(OperationLog::getLog());
    }

    /**
     * @throws Exception
     */
    public function testBatchCreated()
    {
        $data = mockDatas();
        $user = new User();
        $user->insertAll($data);
        $log = batchCreateLog($data) . PHP_EOL;

        $data = mockDatas();
        $user = new User();
        $user->saveAll($data)->each(function ($user) use (&$log) {
            $data = $user->toArray();
            array_unshift($data, $user->id);
            unset($data['id']);
            $log .= createLog($data);
        });

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testUpdated()
    {
        $old = User::find(1);
        $new = mockData();
        $user = new User();
        $user = $user->update(['id' => $old['id']] + $new);
        $log = trim(updateLog($old->toArray(), $new)) . sprintf('，update_time由：%s 改为：%s', $old->update_time, $user->update_time) . PHP_EOL;

        $old = User::find(1);
        $new = mockData();
        $user = new User();
        $user->where('id', $old['id'])->update($new);
        $log .= updateLog($old->toArray(), $new);

        $user = User::find(1);
        $old = $user->toArray();
        $new = mockData();
        $user->name = $new['name'];
        $user->phone = $new['phone'];
        $user->email = $new['email'];
        $user->sex = $new['sex'];
        $user->age = $new['age'];
        $user->save();
        $log .= trim(updateLog($old, $new)) . sprintf('，update_time由：%s 改为：%s', $old['update_time'], $user->update_time) . PHP_EOL;

        assertEquals(OperationLog::getLog(), trim($log));
    }

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     */
    public function testBatchUpdated()
    {
        $old = User::where('id', '<=', 5)->select()->toArray();
        $new = mockData();
        $user = User::update($new, [['id', '<=', 5]]);
        $log = implode(PHP_EOL, array_map(function ($l, $k) use ($old, $user) {
                return trim($l) . sprintf('，update_time由：%s 改为：%s', $old[$k]['update_time'], $user->update_time);
            }, array_filter(explode(PHP_EOL, batchUpdateLog($old, $new))), array_keys($old))) . PHP_EOL;

        $old = User::where('id', '<=', 5)->select()->toArray();
        $new = mockData();
        User::where('id', '<=', 5)->update($new);
        $log .= batchUpdateLog($old, $new);

        $old = User::where('id', '<=', 5)->select()->toArray();
        $new = mockData();
        $news = array_map(function ($u) use ($new) {
            return ['id' => $u['id']] + $new;
        }, $old);
        $user = new User();
        $news = $user->saveAll($news)->toArray();
        array_map(function ($user, $k) use (&$log, $new, $news) {
            $log .= trim(updateLog($user, $new)) . sprintf('，update_time由：%s 改为：%s', $user['update_time'], $news[$k]['update_time']) . PHP_EOL;
        }, $old, array_keys($news));

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testDeleted()
    {
        $user = User::find(1);
        $old = $user->toArray();
        $user->delete();
        $log = deleteLog($old);

        $old = User::find(2)->toArray();
        User::destroy($old['id']);
        $log .= deleteLog($old);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    /**
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function testBatchDeleted()
    {
        $old = User::whereIn('id', [3, 4, 5])->select()->toArray();
        User::whereIn('id', [3, 4, 5])->delete();
        $log = batchDeleteLog($old);

        $old = User::where('id', '<=', 8)->select()->toArray();
        User::destroy(array_column($old, 'id'));
        array_map(function ($user) use (&$log) {
            $log .= deleteLog($user);
        }, $old);

        $old = User::where('id', '<=', 11)->select()->toArray();
        User::destroy(function ($query) {
            $query->where('id', '<=', 11);
        });
        array_map(function ($user) use (&$log) {
            $log .= deleteLog($user);
        }, $old);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testJson()
    {
        $model = new User();
        $model->setJson(['json']);

        $data = mockData();
        $user = clone $model;
        $id = $user->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $old = User::find($id)->toArray();
        $new = mockData();
        $new['json'] = $new;
        $user = clone $model;
        $user->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        $data = mockData();
        $data['json'] = $data;
        $user = clone $model;
        $id = $user->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = User::find($id)->toArray();
        $old['json'] = json_encode(json_decode($old['json'], true), JSON_UNESCAPED_UNICODE);
        $new = mockData();
        $new['json'] = $new;
        $user = clone $model;
        $user->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        $user = User::find($id);
        $old = $user->toArray();
        $user->delete();
        $log .= deleteLog($old);

        $data = mockData();
        $data['json'] = $data;
        $user = clone $model;
        $id = $user->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = User::find($id)->toArray();
        $old['json->name'] = json_decode($old['json'], true)['name'];
        $new = mockData();
        $new = [
            'json->name' => $new['name']
        ];
        $user = clone $model;
        $user->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function testOther()
    {
        $old = User::order('id')->find()->toArray();
        User::where('id', $old['id'])->inc('age')->update();
        $log = updateLog($old, ['age' => '["INC",1]']);

        $old = User::order('id')->find()->toArray();
        $user = new User();
        $user->where('id', $old['id'])->inc('age', 5)->update();
        $log .= updateLog($old, ['age' => '["INC",5]']);

        $old = User::order('id')->find()->toArray();
        $user = new User();
        $user->where('id', $old['id'])->dec('age')->update();
        $log .= updateLog($old, ['age' => '["DEC",1]']);

        $old = User::order('id')->find()->toArray();
        $user = new User();
        $user->where('id', $old['id'])->dec('age', 5)->update();
        $log .= updateLog($old, ['age' => '["DEC",5]']);

        $old = User::order('id')->find()->toArray();
        $user = new User();
        $user->where('id', $old['id'])->update(['age' => Db::raw('age - 1')]);
        $log .= updateLog($old, ['age' => 'age - 1']);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testMultipleDatabases()
    {
        $data = mockData();
        $user = new User();
        $id = $user->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $data = mockData();
        $id = User::connect('default1')->insertGetId($data);
        array_unshift($data, $id);
        $log .= vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testTransaction()
    {
        Db::startTrans();
            $data = mockData();
            $user = new User();
            $id = $user->insertGetId($data);
            array_unshift($data, $id);
            $log = createLog($data);
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Db::startTrans();
            $data = mockData();
            $user = new User();
            $user->insertGetId($data);
        Db::rollback();
        assertEmpty(OperationLog::getLog());

        Db::startTrans();
            $data = mockData();
            $user = new User();
            $id = $user->insertGetId($data);
            array_unshift($data, $id);
            $log = createLog($data);

            Db::startTrans();
                $data = mockData();
                $user = new User();
                $user->insertGetId($data);
            Db::rollback();
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Db::startTrans();
            $data = mockData();
            $user = new User();
            $id = $user->insertGetId($data);
            array_unshift($data, $id);
            $log = createLog($data);

            Db::startTrans();
                $data = mockData();
                $user = new User();
                $id = $user->insertGetId($data);
                array_unshift($data, $id);
                $log .= createLog($data);
            Db::commit();
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Db::startTrans();
            $data = mockData();
            $user = new User();
            $user->insertGetId($data);

            Db::startTrans();
                $data = mockData();
                $user = new User();
                $user->insertGetId($data);
            Db::commit();
        Db::rollback();
        assertEmpty(OperationLog::getLog());

        Db::startTrans();
            $data = mockData();
            $user = new User();
            $id = $user->insertGetId($data);
            array_unshift($data, $id);
            $log = createLog($data);

            Db::startTrans();
                $data = mockData();
                $user = new User();
                $user->insertGetId($data);

                Db::startTrans();
                    $data = mockData();
                    $user = new User();
                    $user->insertGetId($data);
                Db::commit();
            Db::rollback();
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testComment()
    {
        $data = mockData();
        $id = User::connect('default1')->insertGetId($data);
        array_unshift($data, $id);
        $log = vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data) . PHP_EOL;

        $data = mockData();
        $id = Comment::connect('default1')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function testAttribute()
    {
        $data = mockData();
        $user = new User();
        $id = $user->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $data = mockData();
        $user = new Attribute();
        $id = $user->insertGetId($data);
        array_unshift($data, $id);
        $data['sex'] = $user->getSexTextAttr($data['sex']);
        $log .= createLog($data);

        $old = User::order('id')->find()->toArray();
        $new = mockData();
        $user = new User();
        $user->where('id', $old['id'])->update($new);
        $log .= updateLog($old, $new);

        $old = User::order('id')->find($old['id'])->toArray();
        $new['sex'] = (int)!$new['sex'];
        $user = new Attribute();
        $user->where('id', $old['id'])->update($new);
        $old['sex'] = $user->getSexTextAttr($old['sex']);
        $new['sex'] = $user->getSexTextAttr($new['sex']);
        $log .= updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testIgnoreLogFields()
    {
        $data = mockData();
        $user = new User();
        $user->save($data);
        array_unshift($data, $user->id);
        $data['create_time'] = $user->create_time;
        $data['update_time'] = $user->update_time;
        $log = createLog($data);

        $data = mockData();
        $user = new IgnoreLogFields();
        $user->save($data);
        array_unshift($data, $user->id);
        $log .= createLog($data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testDoNotRecordLog()
    {
        $data = mockData();
        $user = new DoNotRecordLog();
        $user->save($data);

        assertEmpty(OperationLog::getLog());
    }

    public function testTableModelMapping()
    {
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $mapping = [
            'test' => [
                'tb_user' => 'Chance\Log\Test\think\model\Attribute'
            ]
        ];
        OperationLog::setTableModelMapping($mapping);
        assertEquals(OperationLog::getTableModelMapping(), $mapping);

        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $data['sex'] = (new Attribute())->getSexTextAttr($data['sex']);
        $log .= createLog($data);
        assertEquals(OperationLog::getLog(), trim($log));

        OperationLog::setTableModelMapping([]);
        assertEmpty(OperationLog::getTableModelMapping());
    }
}