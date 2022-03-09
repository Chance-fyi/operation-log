通过模型事件与获取器，自动生成可读性高的操作日志。

支持 Laravel 的 `illuminate` Orm  与 ThinkPHP 的 `think-orm` 。

### 安装

> composer require chance-fyi/operation-log

### Laravel 使用

```php
<?php

namespace Chance\Log\Test\model;

use Chance\Log\RegisterIlluminateOrmEvent;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // BaseModel中use这个trait
    use RegisterIlluminateOrmEvent;

    public $timestamps = false;
}
```

### ThinkPHP使用

```php
<?php

namespace Chance\Log\Test\model;

use Chance\Log\RegisterThinkOrmEvent;
use think\Model;

class BaseModel extends Model
{
    // BaseModel中use这个trait
    use RegisterThinkOrmEvent;
}
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

**获取器**

设置一个名为`字段名_text`的获取器。

```php
<?php

namespace Chance\Log\Test\model;

class User extends BaseModel
{
    // 日志记录的主键名称
    public string $logKey = 'id';
    
    // Laravel Orm 获取器设置方法
    public function getSexTextAttribute($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
    
    // ThinkPHP Orm 获取器设置方法
    public function getSexTextAttr($key): string
    {
        return ['女','男'][($key ?? $this->sex)] ?? '未知';
    }
}
```

### 获取日志信息

```php
\Chance\Log\OperationLog::getLog();
```

