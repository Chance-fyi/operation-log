支持 Laravel 的 ORM 与 ThinkPHP 的 ORM 。可以生成增、删、改，包括批量增、删、改，以及 使用 DB 操作的日志。

通过~~模型事件~~与获取器，自动生成可读性高的操作日志。2.0 版本已弃用模型事件，因为批量操作没有触发模型事件，使用模型事件无法覆盖所有模型对数据库的操作以及 DB 操作。

### 安装

> composer require chance-fyi/operation-log

### 注意

> 因为使用了单例，所以在常驻内存的框架中使用一定要在每次请求结束之后将生成的日志清空。

### 使用 Laravel 的 ORM

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

### 使用 ThinkPHP 的 ORM

在数据库的配置文件 config/database.php 中增加三个配置项 `query`、`modelNamespace` 和 `logKey`，并修改 `type` 与 `builder`。

```php
<?php

return [
    'default'         => env('database.driver', 'mysql'),
    ...
    'connections'     => [
        'mysql' => [
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
            // 数据库类型
            'type'            => \Chance\Log\orm\think\MySqlConnection::class,
            // 指定查询对象
            "query"           => \Chance\Log\orm\think\Query::class,
            // Builder类
            "builder"         => \think\db\builder\Mysql::class,
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

也可以在模型中通过`$tableComment`与`$columnComment`设置表注释与字段注释。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
    // 表注释
    public $tableComment = '用户';
    // 字段注释
    public $columnComment = [
        'name' => '姓名',
        'sex' => '性别',
    ];
}
```

**获取器**

设置一个名为`字段名_text`的获取器。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
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

### 日志生成忽略的字段

可在模型中通过 `$ignoreLogFields` 设置该表不希望生成日志的字段。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
    // 日志生成忽略的字段
    public $ignoreLogFields = [
        'create_time',
        'update_time',
    ];
}
```

### 数据表不生成日志

可在模型中通过 `$doNotRecordLog` 设置该表不在生成日志。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
    // 不生成该表的日志
    public $doNotRecordLog = true;
}
```

### 表模型映射关系

如果模型文件名与表名不相同，将查找不到表所对应的模型。也就无法完成上面一些，需要在模型中设置的功能。所以可以设置一个表与模型的映射关系，来帮助查找表所对应的模型。

如果是在 ThinkPHP、Laravel、webman 框架中使用，可使用 `php vendor/bin/chance-fyi-operation-log 模型所在目录` 命令来自动构建所选目录中递归查找到的所有模型与表的映射关系。如果命令执行失败，也可选择手动维护映射关系，并通过以下方法手动注入表模型映射关系。

```php
\Chance\Log\facades\OperationLog::setTableModelMapping([
    "database1" => [
        "table1" => "app\\model\\Table1",
        "table2" => "app\\model\\Table2",
    ],
    "database2" => [],
]);
```

### 获取日志信息

```php
\Chance\Log\facades\OperationLog::getLog();
```

### 清除日志信息

```php
\Chance\Log\facades\OperationLog::clearLog();
```

### 启用禁用

```php
# 启用 (默认)
\Chance\Log\facades\OperationLog::enable();
# 禁用
\Chance\Log\facades\OperationLog::disable();
```

### 效果图

![image](https://user-images.githubusercontent.com/37658940/215932487-9c923053-1bdb-4198-a13e-3ca7d668d65c.png)

![image](https://user-images.githubusercontent.com/37658940/215932628-ee02d2d4-b1a0-4fac-a53c-2eda2858c9bc.png)

![image](https://user-images.githubusercontent.com/37658940/215932685-64cf39f3-6ac1-44c1-af29-abc7c078228c.png)

![image](https://user-images.githubusercontent.com/37658940/215932722-99d7ad4b-01d6-4ddc-b47d-9d213c16022e.png)

![image](https://user-images.githubusercontent.com/37658940/215932756-b8a88945-1732-4272-a843-eaf20aea528e.png)

![image](https://user-images.githubusercontent.com/37658940/215932790-b93f54af-7a3e-4098-8765-8821d5d4fcb1.png)
