<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 9:05
 */

namespace Chance\Log\Test\illuminate\model;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

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
    protected $table = 'user';
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    protected $fillable = ['name', 'phone', 'email', 'sex', 'age', 'json'];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}