<?php
/**
 * Created by PhpStorm
 * Date 2023/5/4 9:05.
 */

namespace Chance\Log\Test\hyperf\model;

use DateTimeInterface;
use Hyperf\Database\Model\Model;

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
    public const CREATED_AT = 'create_time';
    public const UPDATED_AT = 'update_time';
    protected ?string $table = 'user';
    protected array $fillable = ['name', 'phone', 'email', 'sex', 'age', 'json'];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}
