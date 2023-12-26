<?php
/**
 * Created by PhpStorm
 * Date 2023/4/27 14:56.
 */

namespace Chance\Log\Test\illuminate;

use Chance\Log\facades\OperationLog;
use Illuminate\Database\Capsule\Manager;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

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
        Manager::table('user')->insert($data);
        array_unshift($data, 1);
        $log = createLog($data);

        $data = mockData();
        Manager::table('user')->insertOrIgnore($data);
        array_unshift($data, 2);
        $log .= createLog($data);

        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        assertEquals(trim($log), OperationLog::getLog());
        assertEmpty(OperationLog::getLog());

        Manager::table('user')->insert(mockData());
        OperationLog::clearLog();
        assertEmpty(OperationLog::getLog());
    }

    public function testBatchCreated()
    {
        $data = mockDatas();
        Manager::table('user')->insert($data);
        $log = batchCreateLog($data) . PHP_EOL;

        $data = mockDatas();
        Manager::table('user')->insertOrIgnore($data);
        $log .= batchCreateLog($data);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testUpdated()
    {
        $old = (array) Manager::table('user')->find(1);
        $new = mockData();
        Manager::table('user')->where('id', $old['id'])->update($new);
        $log = updateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testBatchUpdated()
    {
        $old = Manager::table('user')->where('id', '<=', 5)->get()->map(fn ($v) => (array) $v)->toArray();
        $new = mockData();
        Manager::table('user')->where('id', '<=', 5)->update($new);
        $log = batchUpdateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testDeleted()
    {
        $old = (array) Manager::table('user')->find(1);
        Manager::table('user')->delete($old['id']);
        $log = deleteLog($old);

        $old = (array) Manager::table('user')->find(2);
        Manager::table('user')->where('id', $old['id'])->delete();
        $log .= deleteLog($old);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testBatchDeleted()
    {
        $old = Manager::table('user')->where('id', '<=', 5)->get()->map(fn ($v) => (array) $v)->toArray();
        Manager::table('user')->where('id', '<=', 5)->delete();
        $log = batchDeleteLog($old);

        $old = Manager::table('user')->get()->map(fn ($v) => (array) $v)->toArray();
        Manager::table('user')->delete();
        $log .= batchDeleteLog($old);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testJson()
    {
        $data = mockData();
        $data['json'] = json_encode($data, JSON_UNESCAPED_UNICODE);
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $old = (array) Manager::table('user')->find($id);
        $old['json->name'] = json_decode($old['json'], true)['name'];
        $new = mockData();
        $new = [
            'json->name' => $new['name'],
        ];
        Manager::table('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        $data = mockData();
        $data['json'] = json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = (array) Manager::table('user')->find($id);
        $old['json->data->name'] = json_decode($old['json'], true)['data']['name'];
        $new = mockData();
        $new = [
            'json->data->name' => $new['name'],
        ];
        Manager::table('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testOther()
    {
        $old = (array) Manager::table('user')->first();
        Manager::table('user')->where('id', $old['id'])->increment('age');
        $log = updateLog($old, ['age' => '`age` + 1']);

        $old = (array) Manager::table('user')->first();
        Manager::table('user')->where('id', $old['id'])->increment('age', 5);
        $log .= updateLog($old, ['age' => '`age` + 5']);

        $old = (array) Manager::table('user')->first();
        Manager::table('user')->where('id', $old['id'])->decrement('age');
        $log .= updateLog($old, ['age' => '`age` - 1']);

        $old = (array) Manager::table('user')->first();
        Manager::table('user')->where('id', $old['id'])->decrement('age', 5);
        $log .= updateLog($old, ['age' => '`age` - 5']);

        $old = (array) Manager::table('user')->first();
        $new = ['name' => 'Chance'];
        Manager::table('user')->where('id', $old['id'])->decrement('age', 5, $new);
        $new['age'] = '`age` - 5';
        $log .= updateLog($old, $new);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testMultipleDatabases()
    {
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        $data = mockData();
        $id = Manager::connection('default1')->table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testTransaction()
    {
        Manager::beginTransaction();
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);
        Manager::commit();
        assertEquals(trim($log), OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();
        Manager::table('user')->insertGetId($data);
        Manager::rollback();
        assertEmpty(OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Manager::beginTransaction();
        $data = mockData();
        Manager::table('user')->insertGetId($data);
        Manager::rollback();
        Manager::commit();
        assertEquals(trim($log), OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Manager::beginTransaction();
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);
        Manager::commit();
        Manager::commit();
        assertEquals(trim($log), OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();
        Manager::table('user')->insertGetId($data);

        Manager::beginTransaction();
        $data = mockData();
        Manager::table('user')->insertGetId($data);
        Manager::commit();
        Manager::rollback();
        assertEmpty(OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);

        Manager::beginTransaction();
        $data = mockData();
        Manager::table('user')->insertGetId($data);

        Manager::beginTransaction();
        $data = mockData();
        Manager::table('user')->insertGetId($data);
        Manager::commit();
        Manager::rollback();
        Manager::commit();
        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testStatus()
    {
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log = createLog($data);
        $old = (array) Manager::table('user')->find($id);
        $new = mockData();
        Manager::table('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);
        $old = (array) Manager::table('user')->find($id);
        Manager::table('user')->delete($id);
        $log .= deleteLog($old);

        OperationLog::disable();
        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $new = mockData();
        Manager::table('user')->where('id', $id)->update($new);
        Manager::table('user')->delete($id);
        OperationLog::enable();

        $data = mockData();
        $id = Manager::table('user')->insertGetId($data);
        array_unshift($data, $id);
        $log .= createLog($data);
        $old = (array) Manager::table('user')->find($id);
        $new = mockData();
        Manager::table('user')->where('id', $id)->update($new);
        $log .= updateLog($old, $new);
        $old = (array) Manager::table('user')->find($id);
        Manager::table('user')->delete($id);
        $log .= deleteLog($old);

        assertEquals(trim($log), OperationLog::getLog());
    }

    public function testDefaultDatabaseDoesNotExist()
    {
        Manager::connection('default1')->table('default_database_does_not_exist')->insert([
            'name' => '',
        ]);
        OperationLog::clearLog();

        assertTrue(true);
    }
}
