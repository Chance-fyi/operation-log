<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 9:04.
 */

namespace Chance\Log\Test\illuminate;

use Chance\Log\facades\OperationLog;
use Chance\Log\Test\illuminate\model\Attribute;
use Chance\Log\Test\illuminate\model\Comment;
use Chance\Log\Test\illuminate\model\Connection;
use Chance\Log\Test\illuminate\model\DoNotRecordLog;
use Chance\Log\Test\illuminate\model\IgnoreLogFields;
use Chance\Log\Test\illuminate\model\Timestamps;
use Chance\Log\Test\illuminate\model\User;
use Illuminate\Database\Capsule\Manager;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;

/**
 * @internal
 *
 * @coversNothing
 */
class ModelTest extends TestCase
{
    public function testCreated()
    {
        $data = mockData();
        $user = new User();
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->email = $data['email'];
        $user->sex = $data['sex'];
        $user->age = $data['age'];
        $user->save();
        array_unshift($data, $user->id);
        $data['update_time'] = $user->update_time;
        $data['create_time'] = $user->create_time;
        $log = createLog($data);

        $data = mockData();

        /** @var User $user */
        $user = User::query()->create($data);
        array_unshift($data, $user->id);
        $data['update_time'] = $user->update_time;
        $data['create_time'] = $user->create_time;
        $log .= createLog($data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testBatchCreated()
    {
        for ($i = 0; $i < 10; ++$i) {
            $data = mockData();
            User::query()->create($data);
        }
        OperationLog::clearLog();
        assertEmpty(OperationLog::getLog());
    }

    public function testUpdated()
    {
        $new = mockData();

        /** @var User $user */
        $user = User::query()->find(1);
        $old = $user->toArray();
        $user->name = $new['name'];
        $user->phone = $new['phone'];
        $user->email = $new['email'];
        $user->sex = $new['sex'];
        $user->age = $new['age'];
        $user->save();
        $log = trim(updateLog($old, $new)) . sprintf('，update_time由：%s 改为：%s', $old['update_time'], $old['update_time']) . PHP_EOL;

        $old = $user->toArray();
        $new = mockData();
        User::query()->where('id', 1)->update($new);
        $log .= trim(updateLog($old, $new)) . sprintf('，update_time由：%s 改为：%s', $user->update_time, $user->update_time) . PHP_EOL;

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testBatchUpdated()
    {
        $old = User::query()->where('id', '<=', 5)->get()->toArray();
        $new = mockData();
        User::query()->where('id', '<=', 5)->update($new);
        $log = implode(PHP_EOL, array_map(function ($l, $k) use ($old) {
            $time = $old[$k]['update_time'];

            return trim($l) . sprintf('，update_time由：%s 改为：%s', $time, $time);
        }, array_filter(explode(PHP_EOL, batchUpdateLog($old, $new))), array_keys($old))) . PHP_EOL;

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testDeleted()
    {
        $user = User::query()->find(1);
        $old = $user->toArray();
        $user->delete();
        $log = deleteLog($old);

        $old = User::query()->find(2)->toArray();
        User::destroy($old['id']);
        $log .= deleteLog($old);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testBatchDeleted()
    {
        $old = User::query()->limit(3)->get();
        User::query()->whereIn('id', $old->pluck('id'))->delete();
        $log = batchDeleteLog($old->toArray());

        $old = User::query()->limit(3)->get();
        User::destroy($old->pluck('id'));
        $old->each(function ($user) use (&$log) {
            $log .= deleteLog($user->toArray());
        });

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testJson()
    {
        $data = mockData();
        $data['json'] = json_encode($data, JSON_UNESCAPED_UNICODE);

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        $id = $user->id;
        array_unshift($data, $id);
        $log = createLog($data);

        $old = User::query()->find($id)->toArray();
        $old['json->name'] = json_decode($old['json'], true)['name'];
        $new = mockData();
        $new = [
            'json->name' => $new['name'],
        ];
        Timestamps::query()->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        $data = mockData();
        $data['json'] = json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        $id = $user->id;
        array_unshift($data, $id);
        $log .= createLog($data);

        $old = User::query()->find($id)->toArray();
        $old['json->data->name'] = json_decode($old['json'], true)['data']['name'];
        $new = mockData();
        $new = [
            'json->data->name' => $new['name'],
        ];
        Timestamps::query()->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testOther()
    {
        $old = User::query()->first()->toArray();
        Timestamps::query()->where('id', $old['id'])->increment('age');
        $log = updateLog($old, ['age' => '`age` + 1']);

        $old = User::query()->first()->toArray();
        Timestamps::query()->where('id', $old['id'])->increment('age', 5);
        $log .= updateLog($old, ['age' => '`age` + 5']);

        $old = User::query()->first()->toArray();
        Timestamps::query()->where('id', $old['id'])->decrement('age');
        $log .= updateLog($old, ['age' => '`age` - 1']);

        $old = User::query()->first()->toArray();
        Timestamps::query()->where('id', $old['id'])->decrement('age', 5);
        $log .= updateLog($old, ['age' => '`age` - 5']);

        $old = User::query()->first()->toArray();
        $new = ['name' => 'Chance'];
        Timestamps::query()->where('id', $old['id'])->decrement('age', 5, $new);
        $new['age'] = '`age` - 5';
        $log .= updateLog($old, $new);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testMultipleDatabases()
    {
        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log = createLog($data);

        $data = mockData();

        /** @var Connection $user */
        $user = Connection::query()->create($data);
        array_unshift($data, $user->id);
        $log .= vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testTransaction()
    {
        Manager::beginTransaction();
        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log = createLog($data);
        Manager::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Manager::beginTransaction();
        $data = mockData();
        Timestamps::query()->create($data);
        Manager::rollback();
        assertEmpty(OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log = createLog($data);

        Manager::beginTransaction();
        $data = mockData();
        Timestamps::query()->create($data);
        Manager::rollback();
        Manager::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Manager::beginTransaction();
        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log = createLog($data);

        Manager::beginTransaction();
        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log .= createLog($data);
        Manager::commit();
        Manager::commit();
        assertEquals(OperationLog::getLog(), trim($log));

        Manager::beginTransaction();
        $data = mockData();
        Timestamps::query()->create($data);

        Manager::beginTransaction();
        $data = mockData();
        Timestamps::query()->create($data);
        Manager::commit();
        Manager::rollback();
        assertEmpty(OperationLog::getLog());

        Manager::beginTransaction();
        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log = createLog($data);

        Manager::beginTransaction();
        $data = mockData();
        Timestamps::query()->create($data);

        Manager::beginTransaction();
        $data = mockData();
        Timestamps::query()->create($data);
        Manager::commit();
        Manager::rollback();
        Manager::commit();
        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testComment()
    {
        $mapping = [
            'test1' => [
                'tb_user' => 'Chance\Log\Test\illuminate\model\Comment',
            ],
        ];

        $data = mockData();

        /** @var Comment $user */
        $user = Comment::query()->create($data);
        array_unshift($data, $user->id);
        $log = vsprintf('创建 用户1 (id:%s)：姓名1：%s，手机号1：%s，邮箱1：%s，性别1：%s，年龄1：%s', $data) . PHP_EOL;

        OperationLog::setTableModelMapping($mapping);
        $data = mockData();

        /** @var Comment $user */
        $user = Comment::query()->create($data);
        array_unshift($data, $user->id);
        $log .= createLog($data);
        OperationLog::setTableModelMapping([]);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testAttribute()
    {
        $mapping = [
            'test' => [
                'tb_user' => 'Chance\Log\Test\illuminate\model\Attribute',
            ],
        ];

        $data = mockData();

        /** @var Timestamps $user */
        $user = Timestamps::query()->create($data);
        array_unshift($data, $user->id);
        $log = createLog($data);

        OperationLog::setTableModelMapping($mapping);
        $data = mockData();

        /** @var Attribute $user */
        $user = Attribute::query()->create($data);
        array_unshift($data, $user->id);
        $data['sex'] = $user->getSexTextAttribute($data['sex']);
        $log .= createLog($data);
        OperationLog::setTableModelMapping([]);

        $old = Timestamps::query()->first()->toArray();
        $new = mockData();
        $new['sex'] = (int) !$new['sex'];
        Timestamps::query()->where('id', $old['id'])->update($new);
        $log .= updateLog($old, $new);

        OperationLog::setTableModelMapping($mapping);
        $old = Timestamps::query()->first()->toArray();
        $new['sex'] = (int) !$new['sex'];
        Attribute::query()->where('id', $old['id'])->update($new);
        $user = new Attribute();
        $old['sex'] = $user->getSexTextAttribute($old['sex']);
        $new['sex'] = $user->getSexTextAttribute($new['sex']);
        $log .= updateLog($old, $new);
        OperationLog::setTableModelMapping([]);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testIgnoreLogFields()
    {
        $mapping = [
            'test' => [
                'tb_user' => 'Chance\Log\Test\illuminate\model\IgnoreLogFields',
            ],
        ];

        $data = mockData();

        /** @var IgnoreLogFields $user */
        $user = IgnoreLogFields::query()->create($data);
        array_unshift($data, $user->id);
        $data['update_time'] = $user->update_time;
        $data['create_time'] = $user->create_time;
        $log = createLog($data);

        OperationLog::setTableModelMapping($mapping);
        $data = mockData();

        /** @var IgnoreLogFields $user */
        $user = IgnoreLogFields::query()->create($data);
        array_unshift($data, $user->id);
        $log .= createLog($data);
        OperationLog::setTableModelMapping([]);

        assertEquals(OperationLog::getLog(), trim($log));
    }

    public function testDoNotRecordLog()
    {
        OperationLog::setTableModelMapping([
            'test' => [
                'tb_user' => 'Chance\Log\Test\illuminate\model\DoNotRecordLog',
            ],
        ]);
        $data = mockData();
        DoNotRecordLog::query()->create($data);
        OperationLog::setTableModelMapping([]);

        assertEmpty(OperationLog::getLog());
    }

    public function testTableModelMapping()
    {
        $mapping = [
            'test' => [
                'tb_user' => 'Chance\Log\Test\illuminate\model\DoNotRecordLog',
            ],
        ];
        OperationLog::setTableModelMapping($mapping);
        assertEquals(OperationLog::getTableModelMapping(), $mapping);

        OperationLog::setTableModelMapping([]);
        assertEmpty(OperationLog::getTableModelMapping());
    }
}
