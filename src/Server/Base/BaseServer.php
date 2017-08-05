<?php
/**
 * BaseServer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Base;

use Dybasedev\Keeper\Server\Interfaces\Server;

abstract class BaseServer implements Server
{
    /**
     * 事件注册
     *
     * @return void
     */
    abstract public function eventRegister();
}