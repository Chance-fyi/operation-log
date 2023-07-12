<?php
/**
 * Created by PhpStorm
 * Date 2023/7/7 14:25.
 */

namespace Chance\Log\Test\hyperf;

use Chance\Log\orm\hyperf\MySqlConnection;
use Hyperf\Context\ApplicationContext;
use Hyperf\Database\Connection;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\Connectors\ConnectionFactory;
use Hyperf\Database\Model\Register;
use Hyperf\Pimple\ContainerFactory;
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

        defined('BASE_PATH') ?: define('BASE_PATH', __DIR__ . '/../../');

        $container = (new ContainerFactory())();
        ApplicationContext::setContainer($container);
        self::connections();
        self::truncateTable();
    }

    private static function connections(): void
    {
        $connection = ApplicationContext::getContainer()->get(ConnectionResolver::class);
        $factory = ApplicationContext::getContainer()->get(ConnectionFactory::class);
        Register::setConnectionResolver($connection);
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });
        $connection->addConnection('default', $factory->make([
            'driver' => 'mysql',
            'host' => getenv('MYSQL_HOST') ?: 'mysql',
            'port' => getenv('MYSQL_PORT') ?: 3306,
            'database' => 'test',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'tb_',
        ]));
        $connection->addConnection('default1', $factory->make([
            'driver' => 'mysql',
            'host' => getenv('MYSQL_HOST') ?: 'mysql1',
            'port' => getenv('MYSQL_PORT') ?: 3306,
            'database' => 'test1',
            'username' => 'root',
            'password' => 'root',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'tb_',
        ]));
    }

    private static function truncateTable(): void
    {
        Db::select('truncate table tb_user;');
        Db::connection('default1')->select('truncate table tb_user;');
    }
}
