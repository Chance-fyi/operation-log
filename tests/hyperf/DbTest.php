<?php
/**
 * Created by PhpStorm
 * Date 2023/7/10 13:10.
 */

namespace Chance\Log\Test\hyperf;

use Chance\Log\facades\OperationLog;

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
        Db::table('user')->insert($data);
        array_unshift($data, 1);
        $log = createLog($data);

        $data = mockData();
        Db::table('user')->insertOrIgnore($data);
        array_unshift($data, 2);
        $log .= createLog($data);

        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        assertEquals(OperationLog::getLog(), trim($log));
        assertEmpty(OperationLog::getLog());

        Db::table('user')->insert(mockData());
        OperationLog::clearLog();
        assertEmpty(OperationLog::getLog());
    }

    public function testBatchCreated()
    {
        $data = mockDatas();
        Db::table('user')->insert($data);
        $log = batchCreateLog($data) . PHP_EOL;

        $data = mockDatas();
        Db::table('user')->insertOrIgnore($data);
        $log .= batchCreateLog($data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testUpdated()
    {
        $old = (array)Db::table('user')->find(1);
        $new = mockData();
        Db::table('user')->where('id', $old['id'])->update($new);
        $log = updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testBatchUpdated()
    {
        $old = Db::table('user')->where('id', '<=', 5)->get()->map(fn($v) => (array)$v)->toArray();
        $new = mockData();
        Db::table('user')->where('id', '<=', 5)->update($new);
        $log = batchUpdateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testDeleted()
    {
        $old = (array)Db::table('user')->find(1);
        Db::table('user')->delete($old['id']);
        $log = deleteLog($old);

        $old = (array)Db::table('user')->find(2);
        Db::table('user')->where('id', $old['id'])->delete();
        $log .= deleteLog($old);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testBatchDeleted()
    {
        $old = Db::table('user')->where('id', '<=', 5)->get()->map(fn($v) => (array)$v)->toArray();
        Db::table('user')->where('id', '<=', 5)->delete();
        $log = batchDeleteLog($old);

        $old = Db::table('user')->get()->map(fn($v) => (array)$v)->toArray();
        Db::table('user')->delete();
        $log .= batchDeleteLog($old);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testJson()
    {
        $data = mockData();
        $data['json'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $old = (array)Db::table('user')->find($id);
        $old['json->name'] = json_decode($old['json'], true)['name'];
        $new = mockData();
        $new = [
            'json->name' => $new['name'],
        ];
        Db::table('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        $data = mockData();
        $data['json'] = json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = (array)Db::table('user')->find($id);
        $old['json->data->name'] = json_decode($old['json'], true)['data']['name'];
        $new = mockData();
        $new = [
            'json->data->name' => $new['name'],
        ];
        Db::table('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testOther()
    {
        $old = (array)Db::table('user')->first();
        Db::table('user')->where('id', $old['id'])->increment('age');
        $log = updateLog($old, ['age' => '`age` + 1']);

        $old = (array)Db::table('user')->first();
        Db::table('user')->where('id', $old['id'])->increment('age', 5);
        $log .= updateLog($old, ['age' => '`age` + 5']);

        $old = (array)Db::table('user')->first();
        Db::table('user')->where('id', $old['id'])->decrement('age');
        $log .= updateLog($old, ['age' => '`age` - 1']);

        $old = (array)Db::table('user')->first();
        Db::table('user')->where('id', $old['id'])->decrement('age', 5);
        $log .= updateLog($old, ['age' => '`age` - 5']);

        $old = (array)Db::table('user')->first();
        $new = ['name' => 'Chance'];
        Db::table('user')->where('id', $old['id'])->decrement('age', 5, $new);
        $new['age'] = '`age` - 5';
        $log .= updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testMultipleDatabases()
    {
        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $data = mockData();
        $id = Db::connection('default1')->table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testTransaction()
    {
        Db::beginTransaction();
        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Db::beginTransaction();
        $data = mockData();
        Db::table('user')->insertGetId($data);
        Db::rollback();
        assertEmpty(OperationLog::getLog());

        Db::beginTransaction();
        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Db::beginTransaction();
        $data = mockData();
        Db::table('user')->insertGetId($data);
        Db::rollback();
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Db::beginTransaction();
        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Db::beginTransaction();
        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);
        Db::commit();
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Db::beginTransaction();
        $data = mockData();
        Db::table('user')->insertGetId($data);

        Db::beginTransaction();
        $data = mockData();
        Db::table('user')->insertGetId($data);
        Db::commit();
        Db::rollback();
        assertEmpty(OperationLog::getLog());

        Db::beginTransaction();
        $data = mockData();
        $id = Db::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Db::beginTransaction();
        $data = mockData();
        Db::table('user')->insertGetId($data);

        Db::beginTransaction();
        $data = mockData();
        Db::table('user')->insertGetId($data);
        Db::commit();
        Db::rollback();
        Db::commit();
        assertEquals(OperationLog::getLog(), trim($log));
    }
}
