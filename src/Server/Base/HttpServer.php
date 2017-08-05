<?php
/**
 * HttpServer.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Server\Base;

use Swoole\Http\Request;
use Swoole\Http\Response;

abstract class HttpServer extends BaseServer
{
    /**
     * 请求事件
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return void
     */
    abstract public function onRequest(Request $request, Response $response);
}