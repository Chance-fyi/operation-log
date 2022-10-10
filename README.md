支持 Laravel 的 `illuminate` Orm 与 ThinkPHP 的 `think-orm` 。可以生成增、删、改，包括批量增、删、改，以及 使用 DB 操作的日志。

通过~~模型事件~~与获取器，自动生成可读性高的操作日志。2.0 版本已弃用模型事件，因为批量操作没有触发模型事件，使用模型事件无法覆盖所有模型对数据库的操作以及 DB 操作。

### 安装

> composer require chance-fyi/operation-log

### Laravel 使用

首先在数据库的配置文件 `config/database.php` 中增加两个配置项 `modelNamespace` 和 `logKey`。

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    ...
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            ...
            ...
            // 模型所在的命名空间
            "modelNamespace" => "Chance\Log\Test\model",
            // 日志记录的主键
            "logKey" => "id",
        ],
        ...
    ]
    ...
];
```

然后注册 MySQL 数据库连接的解析器。

```php
\Illuminate\Database\Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
    return new \Chance\Log\orm\illuminate\MySqlConnection($connection, $database, $prefix, $config);
});
```

### ThinkPHP 使用

在数据库的配置文件 config/database.php 中增加三个配置项 `query`、`modelNamespace` 和 `logKey`。

```php
<?php

return [
    'default'         => env('database.driver', 'mysql'),
    ...
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'            => env('database.type', 'mysql'),
            // 服务器地址
            'hostname'        => env('database.hostname', '127.0.0.1'),
            // 数据库名
            'database'        => env('database.database', ''),
            // 用户名
            'username'        => env('database.username', 'root'),
            // 密码
            'password'        => env('database.password', ''),
            // 端口
            'hostport'        => env('database.hostport', '3306'),
            ...
            ...
            // 指定查询对象
            "query"           => \Chance\Log\orm\think\Query::class,
            // 模型所在的命名空间
            "modelNamespace"  => "Chance\Log\Test\model",
            // 日志记录的主键
            "logKey"          => "id",
        ],
        // 更多的数据库配置信息
        ...
    ],
    ...
];
```

### 日志主键

可在模型中设置`$logKey`属性修改需要记录的主键名称。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
    // 日志记录的主键名称
    public string $logKey = 'id';
}
```

### 可读性设置

通过表注释、字段注释与获取器来生成可读性的日志。

**表注释与字段注释**

![image-20220309172842186](https://image.chance.fyi/image-20220309172842186.png)

也可以在模型中通知`$tableComment`与`$columnComment`设置表注释与字段注释。

**获取器**

设置一个名为`字段名_text`的获取器。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
    // 日志记录的主键名称
    public string $logKey = 'id';
    // 表注释
    public $tableComment = "用户";
    // 字段注释
    public $columnComment = [
        'name' => '姓名',
        'sex' => '性别',
    ];

    // Laravel ORM 获取器设置方法
    public function getSexTextAttribute($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }

    // ThinkPHP ORM 获取器设置方法
    public function getSexTextAttr($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}
```

### 获取日志信息

```php
\Chance\Log\facades\OperationLog::getLog();
```

### 清除日志信息

```php
\Chance\Log\facades\OperationLog::clearLog();
```
