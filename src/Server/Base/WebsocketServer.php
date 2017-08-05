<?php
/**
 * WebsocketServer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Base;


use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

abstract class WebsocketServer extends HttpServer
{
    /**
     * 是否开启 HTTP 服务支持
     *
     * @var bool
     */
    protected $enableHttpService = false;

    /**
     * @param bool $switcher
     *
     * @return $this
     */
    public function enableHttp($switcher = true)
    {
        $this->enableHttpService = $switcher;

        return $this;
    }

    /**
     * 消息接收回调
     *
     * @param Server $server
     * @param Frame  $frame
     *
     * @return mixed
     */
    abstract public function onMessage(Server $server, Frame $frame);

}