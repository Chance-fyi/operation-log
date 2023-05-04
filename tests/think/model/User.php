<?php
/**
 * Created by PhpStorm
 * Date 2023/4/28 10:40
 */

namespace Chance\Log\Test\think\model;

use think\Model;

/**
 * @property $id
 * @property $name
 * @property $phone
 * @property $email
 * @property $sex
 * @property $age
 * @property $json
 * @property $create_time
 * @property $update_time
 */
class User extends Model
{
    protected $name = 'user';

    public function setJson($json = [])
    {
        $this->json = $json;
    }
}