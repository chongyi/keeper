<?php
/**
 * Request.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Swoole\Http\Request as SwooleRequest;

/**
 * Class Request
 *
 * 请求
 *
 * @package Dybasedev\Keeper\Http
 */
class Request extends SymfonyRequest
{
    /**
     * 从 Swoole Request 实例创建请求实体
     *
     * @param SwooleRequest $request
     *
     * @return static
     */
    public static function createFromSwooleRequest(SwooleRequest $request)
    {
        $get    = isset($request->get) ? $request->get : [];
        $post   = isset($request->post) ? $request->post : [];
        $files  = isset($request->files) ? $request->files : [];
        $cookie = isset($request->cookie) ? $request->cookie : [];

        if (isset($request->server)) {
            $keys = array_map('strtoupper', array_keys($request->server));
            $server = array_combine($keys, array_values($request->server));
        } else {
            $server = [];
        }

        if (isset($request->header)) {
            $keys = array_map(function ($value) {
                return 'HTTP_' . str_replace('-', '_', strtoupper($value));
            }, $request->header);
            array_merge($server, array_combine($keys, array_values($request->header)));
        }

        return new static($get, $post, [], $cookie, $files, $server);
    }
}