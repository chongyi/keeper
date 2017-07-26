<?php
/**
 * RouteDispatcher.php
 *
 * @copyright Chongyi <xpz3847878@163.com>
 * @link      https://insp.top
 */

namespace Dybasedev\Keeper\Http\Lifecycle\Interfaces;

use Closure;
use Dybasedev\Keeper\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface RouteDispatcher
{
    /**
     * 路由注册
     *
     * @param Closure $callback
     *
     * @return $this
     */
    public function routesRegistrar(Closure $callback);

    /**
     * 调度
     *
     * @param Request $request
     *
     * @return Response
     */
    public function dispatch(Request $request);
}