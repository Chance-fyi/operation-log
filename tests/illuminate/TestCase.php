<?php
/**
 * Created by PhpStorm
 * Date 2023/4/27 14:52.
 */

namespace Chance\Log\Test\illuminate;

use Chance\Log\facades\OperationLog;
use Chance\Log\orm\illuminate\MySqlConnection;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../function.php';
        self::connections();
        self::truncateTable();
    }

    public function setUp(): void
    {
        OperationLog::setTableModelMapping([]);
    }

    private static function connections(): void
    {
        $capsule = new Manager();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('MYSQL_HOST') ?: 'mysql',
            'port' => getenv('MYSQL_PORT') ?: 3306,
            'database' => 'test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'tb_',
        ]);
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv('MYSQL_HOST') ?: 'mysql1',
            'port' => getenv('MYSQL1_PORT') ?: 3306,
            'database' => 'test1',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'tb_',
        ], 'default1');
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
    }

    private static function truncateTable(): void
    {
        Manager::select('truncate table tb_user;');
        Manager::connection('default1')->select('truncate table tb_user;');
    }
}
