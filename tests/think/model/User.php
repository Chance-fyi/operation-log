<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 10:40.
 */

namespace Chance\Log\Test\think\model;

use think\Model;

/**
 * @property int    $id
 * @property int    $name
 * @property string $phone
 * @property string $email
 * @property int    $sex
 * @property int    $age
 * @property string $json
 * @property string $create_time
 * @property string $update_time
 */
class User extends Model
{
    protected $name = 'user';

    public function setJson($json = []): void
    {
        $this->json = $json;
    }
}
