# Keeper

[![Build Status](https://travis-ci.org/chongyi/keeper.svg?branch=master)](https://travis-ci.org/chongyi/keeper)
[![Latest Stable Version](https://poser.pugx.org/chongyi/keeper/v/stable)](https://packagist.org/packages/chongyi/keeper)
[![License](https://poser.pugx.org/chongyi/keeper/license)](https://packagist.org/packages/chongyi/keeper)

基于 Swoole 的后台多进程程序脚手架，提供了基本的进程控制功能。在此基础你将有更多可能使用 PHP 完成一些在 FPM 环境下无法实现的功能。

## 说明

该项目的主要作用不是给一个限定思路下的框架，而是以一个松散的组织形式，提供一系列可用的组件。使用者可以根据需要，既可以利用大量的已有 `Trait`
快速构建一个项目，像用一个框架一样使用；亦可以自行根据已定义的接口自行实现细节逻辑，或以此项目为基础，构建自己的框架。

## 环境要求

* PHP >= 5.6
* Swoole >= 1.8.2

## 使用方法

### 一个简单的 HTTP 服务

1. 先定义一个用作实现 HTTP 服务子进程

> 我们用到了脚手架自带的基于 Laravel Illuminate 路由组件实现的 HTTP 生命周期，
> 这样可以以最少的代码快速实现一个优雅的 Web 程序。

```php
<?php
use Dybasedev\Keeper\Http\ServerProcess;
use Dybasedev\Keeper\Http\Lifecycle\HttpLifecycleTrait;
use Dybasedev\Keeper\Http\Lifecycle\Illuminate\LifecycleTrait;

use Illuminate\Routing\Router;

class Http extends ServerProcess
{
    use HttpLifecycleTrait, LifecycleTrait;

    protected function getRoutesRegistrar()
    {
        return function (Router $router) {
            $router->get('/', function () {
                return 'hello, world';
            });
        };
    }
}
```

2. 创建主进程

```php
<?php
use Dybasedev\Keeper\Process\ProcessManager;

class Master extends ProcessManager
{
    protected function onPreparing() 
    {
        $options = [
            'host'        => '0.0.0.0',
            'port'        => '19730',
            'auto_reload' => false // 该子进程退出后是否自动重载
        ];
        
        // 注册子进程
        $this->registerChildProcess(new Http($options));
    }
    
}
```

3. 启动/重启/停止

```php
<?php
// 确保引入了 autoload.php
// require 'vendor/autoload.php'

$master = (new Master())->setProcessIdFile('./pid')->setDaemon(true);

// 启动
$master->run();

// 重启
$master->restart();

// 停止
$master->stop();
```

## TODO list

> 该清单会随着项目推进而变化，但对于已标示为完成的部分则不会存在较大变动（存在变动往往是由于架构优化进行拆分）。

* [ ] 进程组件
	* [x] 子进程抽象类
	* [x] 进程启停管理器
	* [ ] 运行时进程控制器（目前可用，但仍在完善中）
	* [ ] 子进程通讯服务
* [ ] HTTP 服务端相关组件
	* [x] 子进程抽象类组件
	* [x] 生命周期管理器
	* [x] 路由组件接口和基于 Illuminate\Routing 实现的 Trait
	* [ ] HTTP 会话管理器
	* [ ] WebSocket 子进程扩展抽象类
	* [ ] WebSocket 会话管理器
* [ ] 标准服务端相关组件
	* [ ] TCP UDP 连接以及会话管理器
* [ ] 标准客户端相关组件
* [ ] 传输内容编码解码接口和可快速使用的协议封装 Trait

## License

MIT License