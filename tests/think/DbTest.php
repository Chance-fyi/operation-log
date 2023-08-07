<?php
/**
 * Created by PhpStorm
 * Date 2023/4/26 11:26.
 */

namespace Chance\Log\Test\think;

use Chance\Log\facades\OperationLog;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

/**
 * @internal
 *
 * @coversNothing
 */
class DbTest extends TestCase
{
    public function testCreated()
    {
        $data = mockData();
        Db::table('tb_user')->save($data);
        array_unshift($data, 1);
        $log = createLog($data);

        $data = mockData();
        $id = Db::name('user')->insert($data, true);
        array_unshift($data, $id);
        $log .= createLog($data);

        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        assertEquals(trim($log), OperationLog::getLog());
        assertEmpty(OperationLog::getLog());

        Db::name('user')->save(mockData());
        OperationLog::clearLog();
        assertEmpty(OperationLog::getLog());
    }

    public function testBatchCreated()
    {
        $data = mockDatas();
        Db::name('user')->insertAll($data);
        $log = batchCreateLog($data);

        assertEquals(trim($log), OperationLog::getLog());
    }

    /**
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function testUpdated()
    {
        $old = Db::name('user')->find(1);
        $new = mockData();
        Db::name('user')->save(['id' => $old['id']] + $new);
        $log = updateLog($old, $new);

        $old = Db::name('user')->find(1);
        $new = mockData();
        Db::name('user')->where('id', $old['id'])->save($new);
        $log .= updateLog($old, $new);

        $old = Db::name('user')->find(1);
        $new = mockData();
        Db::name('user')->update(['id' => $old['id']] + $new);
        $log .= updateLog($old, $new);

        $old = Db::name('user')->find(1);
        $new = mockData();
        Db::name('user')->where('id', $old['id'])->update($new);
        $log .= updateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function testBatchUpdated()
    {
        $old = Db::name('user')->where('id', '<=', 5)->select()->toArray();
        $new = mockData();
        Db::name('user')->where('id', '<=', 5)->save($new);
        $log = batchUpdateLog($old, $new);

        $old = Db::name('user')->where('id', '<=', 5)->select()->toArray();
        $new = mockData();
        Db::name('user')->where('id', '<=', 5)->update($new);
        $log .= batchUpdateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function testDeleted()
    {
        $old = Db::name('user')->find(1);
        Db::name('user')->delete($old['id']);
        $log = deleteLog($old);

        $old = Db::name('user')->find(2);
        Db::name('user')->where('id', $old['id'])->delete();
        $log .= deleteLog($old);

        assertEquals(trim($log), OperationLog::getLog());
    }

    /**
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws DataNotFoundException
     */
    public function testBatchDeleted()
    {
        $old = Db::name('user')->whereIn('id', [3, 4, 5])->select()->toArray();
        Db::name('user')->delete(array_column($old, 'id'));
        $log = batchDeleteLog($old);

        $old = Db::name('user')->where('id', '<=', 7)->select()->toArray();
        Db::name('user')->where('id', '<=', 7)->delete();
        $log .= batchDeleteLog($old);

        assertEquals(trim($log), OperationLog::getLog());
    }

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function testJson()
    {
        $data = mockData();
        $id = Db::name('user')->json(['json'])->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $old = Db::name('user')->find($id);
        $new = mockData();
        $new['json'] = $new;
        Db::name('user')->where('id', $id)->json(['json'])->update($new);
        $new['json'] = json_encode($new['json'], JSON_UNESCAPED_UNICODE);
        $log .= updateLog($old, $new);

        $data = mockData();
        $data['json'] = $data;
        $id = Db::name('user')->json(['json'])->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = Db::name('user')->find($id);
        $old['json'] = json_encode(json_decode($old['json'], true), JSON_UNESCAPED_UNICODE);
        $new = mockData();
        $new['json'] = $new;
        Db::name('user')->where('id', $id)->json(['json'])->update($new);
        $new['json'] = json_encode($new['json'], JSON_UNESCAPED_UNICODE);
        $log .= updateLog($old, $new);

        $old = Db::name('user')->find($id);
        Db::name('user')->delete($id);
        $log .= deleteLog($old);

        $data = mockData();
        $data['json'] = $data;
        $id = Db::name('user')->json(['json'])->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = Db::name('user')->find($id);
        $old['json->name'] = json_decode($old['json'], true)['name'];
        $new = mockData();
        $new = [
            'json->name' => $new['name'],
        ];
        Db::name('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    /**
     * @throws ModelNotFoundException
     * @throws DataNotFoundException
     * @throws DbException
     */
    public function testOther()
    {
        $old = Db::name('user')->order('id')->find();
        Db::name('user')->where('id', $old['id'])->inc('age')->update();
        $log = updateLog((array) $old, ['age' => '["INC",1]']);

        $old = Db::name('user')->order('id')->find();
        Db::name('user')->where('id', $old['id'])->inc('age', 5)->update();
        $log .= updateLog((array) $old, ['age' => '["INC",5]']);

        $old = Db::name('user')->order('id')->find();
        Db::name('user')->where('id', $old['id'])->dec('age')->update();
        $log .= updateLog((array) $old, ['age' => '["DEC",1]']);

        $old = Db::name('user')->order('id')->find();
        Db::name('user')->where('id', $old['id'])->dec('age', 5)->update();
        $log .= updateLog((array) $old, ['age' => '["DEC",5]']);

        $old = Db::name('user')->order('id')->find();
        Db::name('user')->where('id', $old['id'])->update(['age' => Db::raw('age - 1')]);
        $log .= updateLog((array) $old, ['age' => 'age - 1']);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testMultipleDatabases()
    {
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $data = mockData();
        $id = Db::connect('default1')->name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testTransaction()
    {
        Db::startTrans();
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);
        Db::commit();
        assertEquals(trim($log), OperationLog::getLog());

        Db::startTrans();
        $data = mockData();
        Db::name('user')->insertGetId($data);
        Db::rollback();
        assertEmpty(OperationLog::getLog());

        Db::startTrans();
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Db::startTrans();
        $data = mockData();
        Db::name('user')->insertGetId($data);
        Db::rollback();
        Db::commit();
        assertEquals(trim($log), OperationLog::getLog());

        Db::startTrans();
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Db::startTrans();
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);
        Db::commit();
        Db::commit();
        assertEquals(trim($log), OperationLog::getLog());

        Db::startTrans();
        $data = mockData();
        Db::name('user')->insertGetId($data);

        Db::startTrans();
        $data = mockData();
        Db::name('user')->insertGetId($data);
        Db::commit();
        Db::rollback();
        assertEmpty(OperationLog::getLog());

        Db::startTrans();
        $data = mockData();
        $id = Db::name('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Db::startTrans();
        $data = mockData();
        Db::name('user')->insertGetId($data);

        Db::startTrans();
        $data = mockData();
        Db::name('user')->insertGetId($data);
        Db::commit();
        Db::rollback();
        Db::commit();
        assertEquals(trim($log), OperationLog::getLog());
    }
}
