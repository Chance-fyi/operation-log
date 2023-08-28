<?php
/**
 * Created by PhpStorm
 * Date 2023/8/28 11:46.
 */

namespace Chance\Log\Test\illuminate;

use Chance\Log\facades\OperationLog;
use Chance\Log\Test\illuminate\model\Casts;

use function PHPUnit\Framework\assertEquals;

/**
 * @internal
 *
 * @coversNothing
 */
class BugFixesTest extends TestCase
{
    public function test5()
    {
        OperationLog::setTableModelMapping([
            'test' => [
                'tb_user' => 'Chance\Log\Test\illuminate\model\Casts',
            ],
        ]);

        $data = mockData();
        $data['json'] = $data;

        /** @var Casts $user */
        $user = Casts::query()->create($data);
        $id = $user->id;
        array_unshift($data, $id);
        $log = createLog($data);

        $user = Casts::query()->find($id);
        $old = $user->toArray();
        $new = mockData();
        $new['json'] = array_merge($old['json'], ['name' => $new['name']]);
        Casts::query()->where('id', $id)->update($new);
        $log .= updateLog($old, $new);

        $data = Casts::query()->find($id)->toArray();
        Casts::query()->where('id', $id)->delete();
        $log .= deleteLog($data);

        OperationLog::setTableModelMapping([]);

        assertEquals(trim($log), OperationLog::getLog());
    }
}
