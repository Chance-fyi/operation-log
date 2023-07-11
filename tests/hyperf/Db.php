<?php
/**
 * Created by PhpStorm
 * Date 2023/7/10 13:32.
 */

namespace Chance\Log\Test\hyperf;

use Closure;
use Generator;
use Hyperf\Context\ApplicationContext;
use Hyperf\Database\ConnectionInterface;
use Hyperf\Database\ConnectionResolver;
use Hyperf\Database\Query\Builder;
use Hyperf\Database\Query\Expression;

/**
 * DB Helper.
 *
 * @method static Builder             table(string $table)
 * @method static Expression          raw($value)
 * @method static mixed               selectOne(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static array               select(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static Generator           cursor(string $query, array $bindings = [], bool $useReadPdo = true)
 * @method static bool                insert(string $query, array $bindings = [])
 * @method static int                 update(string $query, array $bindings = [])
 * @method static int                 delete(string $query, array $bindings = [])
 * @method static bool                statement(string $query, array $bindings = [])
 * @method static int                 affectingStatement(string $query, array $bindings = [])
 * @method static bool                unprepared(string $query)
 * @method static array               prepareBindings(array $bindings)
 * @method static mixed               transaction(Closure $callback, int $attempts = 1)
 * @method static void                beginTransaction()
 * @method static void                rollBack()
 * @method static void                commit()
 * @method static int                 transactionLevel()
 * @method static array               pretend(Closure $callback)
 * @method static ConnectionInterface connection(string $pool)
 */
class Db
{
    public static function __callStatic($name, $arguments)
    {
        $db = ApplicationContext::getContainer()->get(ConnectionResolver::class);
        if ('connection' === $name) {
            return $db->connection(...$arguments);
        }

        return $db->connection()->{$name}(...$arguments);
    }
}
