<?php
/**
 * Server.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Interfaces;


interface Server
{
    /**
     * @return \Swoole\Server
     */
    public function createSwooleServer();
}