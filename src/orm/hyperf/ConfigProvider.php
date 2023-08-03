<?php
/**
 * Created by PhpStorm
 * Date 2023/8/3 17:15.
 */

namespace Chance\Log\orm\hyperf;

use Chance\Log\orm\hyperf\aspect\NewBaseQueryBuilderAspect;
use Hyperf\Database\Connection;

class ConfigProvider
{
    public function __invoke(): array
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        });

        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'aspects' => [
                NewBaseQueryBuilderAspect::class,
            ],
        ];
    }
}
