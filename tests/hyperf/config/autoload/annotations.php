<?php
/**
 * Created by PhpStorm
 * Date 2023/7/12 10:40.
 */

return [
    'scan' => [
        'paths' => [
            BASE_PATH . '/src',
        ],
        'ignore_annotations' => [
            'mixin',
        ],
        'class_map' => [
            'Hyperf\Database\Model\Model' => 'vendor/hyperf/database/src/Model/Model.php',
        ],
    ],
];
